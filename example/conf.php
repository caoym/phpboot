<?php
return [
    "phprs\\Router"=>[
        "properties"=>[
            "export_apis"=>true //Enable output api documents, if true, visit http://your-host:port/apis/ to get documents
            //,"hooks":["MyHook1","MyHook2"] //Enable hooks
            ,"url_begin"=>1 // if url is "/abc/123", then 1st path node "/abc/" will be ignored.
            ,"api_path"=>__DIR__.'/apis/'
        ]
    ],
    "phprs\\Invoker"=>[
        "properties"=>[
            //"cache":"@invokeCache"  //cache result of apis(the apis with @cache)
        ] 
    ]
    
    //if the property "cache" of phprs\\Invoker is set
    /**
    ,
    "invokeCache"=>[
    	"class"=>"phprs\\util\\RedisCache",
		"singleton"=>true,
    	"properties"=>[
    		"serialize"=>true,
    		"host"=>"127.0.0.1",
    		"port"=>"6379"
    	]
    ]
    */
     
    //db config sample, and ezphp can work with PDO simply
    /**
    ,
    "db"=>[
		"singleton"=>true,
    	"class"=>"PDO",
    	"pass_by_construct"=>true,
    	"properties"=>[
    		"dsn"=>"mysql:host=127.0.0.1;dbname=xxxxxx;charset=UTF8",
    		"username"=>"xxxxxx",
    		"passwd"=>"xxxxxx"  		
    	]
    ]
    */
];	
