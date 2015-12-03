<?php
/**
 * 此示例只用于描述接口定义方法，来自其他项目，代码不全，暂无法运行
 */
use caoym\util\Verify;
use caoym\util\exceptions\Forbidden;
use caoym\util\Logger;
use caoym\util\exceptions\NotFound;
use caoym\ezsql\Sql;
use caoym\util\exceptions\BadRequest;
use caoym\util\Curl;

/**
 * 昵称冲突
 * @author caoym
 */
class AliasConflict extends \Exception
{
}
/**
 * 用户名冲突
 * @author caoym
 */
class AccountConflict extends \Exception
{
}
/**
 * 
 * @author caoym
 * 用户信息管理
 * @path("/users")
 */
class Users
{

    /**
     * 创建用户
     * @route({"POST","/"}) 新建用户
     * @param({"account", "$._POST.mobile"})  手机号，必选
     * @param({"password", "$._POST.password"})  密码，必选
     * @param({"alias", "$._POST.alias"})  昵称，必选
     * @param({"avatar", "$._FILES.avatar.tmp_name"})  头像文件，可选
     * @param({"token", "$._COOKIE.token"}) 验证短信验证码后获取的cookie
     * 
     * @throws({"caoym\util\exceptions\Forbidden","status", "403 Forbidden"}) cookie失效
     * @throws({"caoym\util\exceptions\Forbidden","body", {"error":"Forbidden"}}) cookie失效
     * 
     * @throws({"AliasConflict","status", "409 Conflict"}) 昵称冲突
     * @throws({"AliasConflict","body", {"error":"AliasConflict"}}) 昵称冲突
     * 
     * @throws({"AccountConflict","status", "409 Conflict"}) 用户名冲突(手机号冲突)
     * @throws({"AccountConflict","body", {"error":"AccountConflict"}}) 用户名冲突(手机号冲突)
     * 
     * @return({"cookie","uid","$uid","+365 days","/"})  uid
     * @return 返回用户id
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
            $avatar = '';//数据库设置了not null
        }
        $pdo = $this->db;
        $pdo->beginTransaction();
        try {
            //检查账号是否重复（用户名、邮箱、手机号任一一项都不能重复）
            //由于数据库中已近存在重复记录，所以不能设置数据库的唯一性索引、
            $res = Sql::select('uid')->from('uc_members')->where(
                'username = ? OR email = ? OR mobile = ?', $account,$account,$account
                )->forUpdate()->get($pdo);
            Verify::isTrue(count($res) ==0, new AccountConflict("account $account conflict"));
            // 昵称
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
     * 修改用户
     * @route({"POST","/current"}) 
     * 
     * @param({"password", "$._POST.password"})  密码，可选
     * @param({"alias", "$._POST.alias"})  昵称，可选
     * @param({"avatar", "$._FILES.avatar.tmp_name"})  头像，需要更新头像时指定，可选
     * @param({"token", "$._COOKIE.token"}) 登录后获取的cookie
     *
     * @throws({"caoym\util\exceptions\Forbidden","status", "403 Forbidden"}) code验证失败
     * @throws({"caoym\util\exceptions\Forbidden","body", {"error":"Forbidden"}}) code验证失败
     * 
     * @throws({"AliasConflict","status", "409 Conflict"}) 昵称冲突
     * @throws({"AliasConflict","body", {"error":"AliasConflict"}}) 昵称冲突
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
                    //昵称不能重复
                    $res = Sql::select('uid')->from('pre_common_member_profile')->where('realname = ? AND uid <> ?', $alias, $uid)->forUpdate()->get($pdo);
                    Verify::isTrue(count($res) ==0, new AliasConflict("alias $alias conflict"));
                    
                    $params['realname'] = $alias;
                }
                //TODO 头像
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
     * 批量获取用户信息
     * @route({"GET","/"}) 
     * @param({"uids","$._GET.uids"}) 一组用户id
     * @return("body")
     * 返回用户信息
     *  [
     *  {
     *      "uid":"id",
     *       "avatar":"头像",
     *       "alias":"昵称",
     *       "level":2, //0, 普通用户   1,未审核研究员    2,研究员
     *       "fields":"农林牧渔", //领域 ,level>0时有效
     *       "company":"公司名", //level>0时有效
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

        if(is_array($res)){
            foreach ($res as &$i){
                $ext = json_decode($i['ext'],true);
                unset($i['ext']);
                if(isset($ext['education'])) $i['education'] = $ext['education'];
                if(isset($ext['company'])) $i['company'] = $ext['company'];
                if(isset($ext['fields'])) $i['fields'] = $ext['fields']; 
            }
        }
        return $res;
    }
    
    /**
     * 获取当前用户信息
     * @route({"GET","/current"})
     * 
     * @param({"token", "$._COOKIE.token"}) 登录后获取的cookie
     * @return("body")
     * 返回用户信息
     *  {
     *      "uid":"id",
     *      "avatar":"头像",
     *      "alias":"昵称",
     *      "level":2, //0, 普通用户   1,未审核研究员    2,研究员
     *      "fields":"农林牧渔", //领域 ,level>0时有效
     *      "company":"公司名", //level>0时有效
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
        //根据当前uid获取自选股列表
        $portfolio = $this->factory->create('Portfolio');
        $stockList = $portfolio->getStockList($uid);
        $res[0]['stocklist'] = $stockList;
        return $res[0];
    }
    
    /**
     * 通过账号获取用户信息
     */
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
    /**
     * 通过账号获取uid
     */
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
    /**
     * 删除用户
     * 只用于单元测试
     */
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
    /**
     * 上传头像
     * @param string $file 本地路径
     * @return string 返回头像url
     */
    private function uploadAvatar($file){
         //上传头像
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
    /** @property({"default":"@utils\ObjectStoreService"}) */
    public $oss;
    
    /**
     * @property({"default":"@\utils\Curl"})
     * @var \utils\Curl
     */
    private $httpClient;
}
