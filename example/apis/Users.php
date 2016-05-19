<?php
/**
 * a sample for showing annotations
 */
use caoym\util\Verify;
use caoym\util\exceptions\Forbidden;
use caoym\util\Logger;
use caoym\util\exceptions\NotFound;
use caoym\ezsql\Sql;
use caoym\util\exceptions\BadRequest;

/**
 * @author caoym
 */
class AliasConflict extends \Exception
{
}
/**
 * @author caoym
 */
class AccountConflict extends \Exception
{
}
/**
 * 
 * users manager
 * @path("/users")
 */
class Users
{

    /**
     * create user
     * @route({"POST","/"}) 
     * @param({"account", "$._POST.mobile"})  cell-phone number, required
     * @param({"password", "$._POST.password"})  password, required
     * @param({"alias", "$._POST.alias"})  user's alias, required
     * @param({"avatar", "$._FILES.avatar.tmp_name"})  user's avatar, optional
     * @param({"token", "$._COOKIE.token"})  
     * 
     * @throws({"caoym\util\exceptions\Forbidden","res", "403 Forbidden",{"error":"Forbidden"}}) cookie invalid
     * 
     * @throws({"AliasConflict","res", "409 Conflict",{"error":"AliasConflict"}}) alias conflict
     * 
     * @throws({"AccountConflict","res", "409 Conflict",{"error":"AccountConflict"}}) account conflict
     * 
     * @return({"cookie","uid","$uid","+365 days","/"})  uid
     * @return user's id
     * {"uid":"1233"}
     */
    public function createUser(&$uid, $token, $account, $alias, $password, $avatar = null){
        $tokens = $this->factory->create('Tokens');
        $token = $tokens->getToken($token);
        Verify::isTrue(!$token['uid'], new BadRequest('invalid token'));
        Verify::isTrue($token['account'] == $account, new Forbidden('invalid mobile '.$account));

        if($avatar){
            $avatar = $this->uploadAvatar($avatar);
        }else{
            $avatar = '';
        }
        $pdo = $this->db;
        $pdo->beginTransaction();
        try {
            //is account conflict
            $res = Sql::select('uid')->from('uc_members')->where(
                'username = ? OR email = ? OR mobile = ?', $account,$account,$account
                )->forUpdate()->get($pdo);
            Verify::isTrue(count($res) ==0, new AccountConflict("account $account conflict"));
            //is avatar conflict
            $res = Sql::select('uid')->from('pre_common_member_profile')->where('realname = ?', $alias)->forUpdate()->get($pdo);
            Verify::isTrue(count($res) ==0, new AliasConflict("alias $alias conflict"));
            
            $uid = Sql::insertInto('uc_members')->values(['username'=>$account,
                'password'=>$password,
                'regdate'=>Sql::native('UNIX_TIMESTAMP(now())'),
                'salt'=>''
            ])->exec($pdo)->lastInsertId();
            
            Sql::insertInto('pre_common_member_profile')->values([
                'realname'=>$alias,
                'uid'=>$uid,
                'avatar'=>$avatar
            ])->exec($pdo);
            
            $pdo->commit();
        } catch (Exception $e) {
            Logger::warning("createUser($account) failed with ".$e->getMessage());
            $pdo->rollBack();
            throw $e;
        }
        $token['uid'] = $uid;
        $tokens->updateToken($token, $token);
        return ['uid'=>$uid];
    }
    
    /**
     * modify user's information
     * @route({"POST","/current"}) 
     * 
     * @param({"password", "$._POST.password"}) modify password, optional
     * @param({"alias", "$._POST.alias"})  modify alias, optional
     * @param({"avatar", "$._FILES.avatar.tmp_name"})  modify avatar, optional
     * @param({"token", "$._COOKIE.token"}) used for auth
     *
     * @throws({"caoym\util\exceptions\Forbidden","res", "403 Forbidden", {"error":"Forbidden"}}) invalid cookie
     * 
     * @throws({"AliasConflict","status", "409 Conflict", {"error":"AliasConflict"}}) alias conflict
     * 
     */
    public function updateUser($token, $alias=null, $password=null, $avatar=null ){

        $token = $this->factory->create('Tokens')->getToken($token);
        Verify::isTrue(isset($token['uid']) && $token['uid']!=0, new Forbidden("invalid uid {$token['uid']}"));
        if($avatar){
            $avatar = $this->uploadAvatar($avatar);
        }
        $uid = $token['uid'];
        
        $pdo = $this->db;
        $pdo->beginTransaction();
        try {
            if($alias || $avatar){
                $sets = array();
                $params = array();
                
                if($alias){
           
                    $res = Sql::select('uid')->from('pre_common_member_profile')->where('realname = ? AND uid <> ?', $alias, $uid)->forUpdate()->get($pdo);
                    Verify::isTrue(count($res) ==0, new AliasConflict("alias $alias conflict"));
                    
                    $params['realname'] = $alias;
                }
               
                if($avatar){
                    $params['avatar'] = $avatar;
                }
                Sql::update('pre_common_member_profile')->setArgs($params)->where('uid = ?',$uid)->exec($pdo);
            }
            
            if($password !== null){
                Sql::update('uc_members')->setArgs([
                        'password'=>$password, 
                        'salt'=>''
                    ])->where('uid=?',$uid)->exec($pdo);
                
            }
            
            $pdo->commit();
        } catch (Exception $e) {
            Logger::warning("updateUser($uid) failed with ".$e->getMessage());
            $pdo->rollBack();
            throw $e;
        }
    }
    
    /**
     * get users info
     * @route({"GET","/"}) 
     * @param({"uids","$._GET.uids"}) users id
     * @return("body")
     * response like this:
     *  [
     *  {
     *      "uid":"id",
     *      "avatar":"http://xxxxx/avatar.jpg",
     *      "alias":"caoym",
     *  }
     *  ...
     *  ]
     */
    public function getUserByIds($uids, $asDict=false) {
        if(count($uids) == 0){
            return [];
        }

        $res = Sql::select('uc_members.uid',
            'pre_common_member_profile.realname as alias', 
            'pre_common_member_profile.avatar', 
            'pre_common_member_profile.level',
            'pre_common_member_profile.ext')
            ->from('uc_members')
            ->leftJoin('pre_common_member_profile')
            ->on('uc_members.uid=pre_common_member_profile.uid')
            ->where('uc_members.uid IN (?)', $uids)
            ->get($this->db ,$asDict?'uid':null);

        return $res;
    }
    
    /**
     * get current user info
     * @route({"GET","/current"})
     * 
     * @param({"token", "$._COOKIE.token"}) 
     * @return("body")
     * response like this:
     *  {
     *      "uid":"id",
     *      "avatar":"http://xxxxx/avatar.jpg",
     *      "alias":"caoym"
     *  }
     *  ...
     */
    public function getCurrentUser($token){
        $tokens = $this->factory->create('Tokens');
        $token = $tokens->getToken($token);
        $uid = $token['uid'];
        Verify::isTrue($token['uid'] , new Forbidden('invalid uid '.$uid));
        $res = $this->getUserByIds([$uid]);
        Verify::isTrue(count($res) !=0, new NotFound("user $uid not found"));
        return $res[0];
    }
    
    public function getUserByAccount($account){
        return $this->getUser(['uc_members.username'=>$account]); 
    }
    public function getUserByAlias($alias){
        return $this->getUser(['pre_common_member_profile.realname'=>$alias]);
    }
    
    private function getUser($cond){
        $res = Sql::select('uc_members.uid',
            'pre_common_member_profile.realname as alias',
            'pre_common_member_profile.avatar',
            'pre_common_member_profile.level',
            'pre_common_member_profile.ext')
            ->from('uc_members')
            ->leftJoin('pre_common_member_profile')
            ->on('uc_members.uid=pre_common_member_profile.uid')
            ->whereArgs($cond)
            ->get($this->db);
        if(count($res)){
            $ext = json_decode($res[0]['ext'],true);
            unset($res[0]['ext']);
        
            if(isset($ext['education'])) $res[0]['education'] = $ext['education'];
            if(isset($ext['company'])) $res[0]['company'] = $ext['company'];
            if(isset($ext['fields'])) $res[0]['fields'] = $ext['fields'];
        }
        return count($res)>0 ? $res[0]:false;
    }

    public function verifyPassword($account, $password){
        $res = Sql::select('uid, password, salt')->from('uc_members')
        ->where('username = ? or email = ?', $account, $account)
        ->get($this->db);
        
        if(count($res) == 0){
            return false;
        }
       
        if($res[0]['password'] == $password ){
            return $res[0]['uid'];
        }
        return false;
    }

    public function deleteUserByAccount($account){
        $this->db->beginTransaction();
        try {
            $res = Sql::select('uid')->from('uc_members')->where('username=?',$account)->forUpdate()->get($this->db);
            if (count($res) == 0){
                $this->db->rollBack();
                return false;
            }
            $uid = $res[0]['uid'];
            Sql::deleteFrom('pre_common_member_profile')->where('uid=?',$uid)->exec($this->db);
            Sql::deleteFrom('uc_members')->where('uid=?',$uid)->exec($this->db);
            $this->db->commit();
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
        return true;
    }

    private function uploadAvatar($file){
        $name = md5_file($file);
        return $this->oss->upload("avatars", $name, $file);
    }
    /** @inject("ioc_factory") */
    private $factory;
    /**
     * @property({"default":"@db"})  
     * @var PDO
     */
    public $db;
    /** @property({"default":"@util\ObjectStoreService"}) */
    public $oss;
    
}
