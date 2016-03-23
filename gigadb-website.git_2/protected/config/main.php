<?php
$esConfig = json_decode(file_get_contents(dirname(__FILE__).'/es.json'), true);
$dbConfig = json_decode(file_get_contents(dirname(__FILE__).'/db.json'), true);
$pre_config = require(dirname(__FILE__).'/local.php');

// Location where user images are stored
Yii::setPathOfAlias('uploadPath',dirname(__FILE__).DIRECTORY_SEPARATOR.'../../images/uploads');
Yii::setPathOfAlias('uploadURL', '/images/uploads/');
Yii::setPathOfAlias('Elastica', realpath(dirname(__FILE__). '/../../Elastica/lib'));
Yii::setPathOfAlias('googleAPI', realpath(dirname(__FILE__).'/../../google-api-php-client/src'));
Yii::setPathOfAlias('scholar', realpath(dirname(__FILE__).'/../scripts/scholar.py'));

return CMap::mergeArray(array(
    'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
    'name'=>'GigaDB',

    'preload'=>array(
        'log',
        'bootstrap',
    ),

    'import'=>array(
        'application.models.*',
        'application.components.*',
        'application.behaviors.*',
        'application.vendors.*',
        'application.helpers.*',
    ),
    
    'modules'=>array(
        'gii'=>array(
            'class'=>'system.gii.GiiModule',
            'password'=>'gigadbyii',
            'ipFilters'=>array('*.*.*.*'),
        ),
        'opauth' => array(
            'opauthParams' => array(
                'Security.salt' => '1234',
                'Strategy' => array(
                    'Facebook' => array(
                        'app_id' => '1692038101028925',
                        'app_secret' => '108c560a483c75f02059bddd7233186c',
                    ),
                    'LinkedIn' => array(
                        'api_key' => '7505pj8dhj1s9u',
                        'secret_key' => 'o6qJkk0PByetVmzo',
                    ),
                    'Google' => array(
                        'client_id' => '503810240048-om03ev8gjdm0orn5fjj1ojvgq98sh60h.apps.googleusercontent.com',
                        'client_secret' => '4lqCG_xhCDdFqd8lfjEGzjnq',
                    ),
                    'Twitter' => array(
                        'key' => '06Hdsh6YcXfkmC4LgcCM8VOCK',
                        'secret' => '7kKnmx6R1uQD7rnrDOYV1n4xu0rc0d2zM280U76tqsZZ4ImVoL',
                    ),
                    'Orcid' => array(
                        'client_id' => 'APP-ILT4DHDKKMORDP53',
                        'client_secret' => '4df27eaa-01c8-4fca-b02a-74e7d6f729af',
                    ),
                ),
            ),
        ),
    ),

    'components'=>array(
        'db'=>array(
            'class'=>'system.db.CDbConnection',
            'connectionString'=>"pgsql:dbname={$dbConfig['database']};host={$dbConfig['host']}",
            'username'=>$dbConfig['user'],
            'password'=>$dbConfig['password'],
            'charset'=>'utf8',
            'persistent'=>true,
            'enableParamLogging'=>true,
            'schemaCachingDuration'=>30
        ),
        'request' => array(
            //'enableCookieValidation' => true,
        ),

        'bootstrap'=>array(
            'class'=>'ext.bootstrap.components.Bootstrap',
        ),
        'cache' => array(
            'class' => 'system.caching.CFileCache'
        ),
        'session' => array(
            'class' => 'system.web.CDbHttpSession',
            'connectionID' => 'db',
            'timeout' => 3600,
        ),
        'errorHandler'=>array(
            'errorAction'=>'site/error',
        ),
        'urlManager'=>array(
            'urlFormat'=>'path',
            'showScriptName'=>false,
            'rules'=>array(
                '/dataset/<id:\d+>'=>'dataset/view/id/<id>',
                '/dataset/<id:\d+>/<slug:.+>'=>'dataset/view/id/<id>',
                //'search'=>'site/index',
                //'download/<search:.+>'=>'site/index',
                //'download'=>'site/index',
                '.*'=>'site/index',
            ),
        ),
        'log'=>array(
            'class'=>'CLogRouter',
            'routes'=>array(
                array(
                    'class'=>'CFileLogRoute',
                    'levels'=>'error, warning, info, debug',
                ),
                //array(
                //    'class'=>'CWebLogRoute',
                //),
            ),
        ),
        'elastic' => array(
            'class' => 'Elastic',
            'host' => $esConfig['host'],
            'port' => $esConfig['port']
        ),
        
        'messages'=>array(
            'class'=>'CPhpMessageSource',
        ),
        'user'=>array(
            // enable cookie-based authentication
            'allowAutoLogin'=>true,
            //User WebUser
            'class'=>'WebUser',
        ),
        'authManager'=>array(
            'class'=>'CDbAuthManager',
            'connectionID'=>'db',
        ),
        'image'=>array(
          'class'=>'application.extensions.image.CImageComponent',
              // GD or ImageMagick
          'driver'=>'GD',
      ),
    ),

    'params' => array(
        # ES search
        'es_search' => array(
            'limits' => array(
                'default' => 10,
                'dataset' => 20,
                'file' => 20,
            ),
        ),
        'scholar_query' => 'http://scholar.google.com/scholar?q=',
        'ePMC_query' => "http://europepmc.org/search?scope=fulltext&query=",
        // date formats
        'js_date_format' => 'dd-mm-yy',
        'db_date_format' => "%Y-%m-%d",
        'display_date_format' => "%gggggggd-%m-%Y",
        'display_short_date_format' => "%d-%m",
        'language' => 'en' ,
        'languages' => array('en' => 'EN', 'zh_tw' => 'TW'),
   ),
), $pre_config);

