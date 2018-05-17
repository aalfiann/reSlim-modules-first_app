<?php
//Define interface class for router
use \Psr\Http\Message\ServerRequestInterface as Request;        //PSR7 ServerRequestInterface   >> Each router file must contains this
use \Psr\Http\Message\ResponseInterface as Response;            //PSR7 ResponseInterface        >> Each router file must contains this

//Define your modules class
use \modules\first_mod\FirstMod as FirstMod;                    //Your main modules class

//Define additional class for any purpose
use \classes\middleware\ApiKey as ApiKey;                       //ApiKey Middleware             >> To authorize request by using ApiKey generated by reSlim
use \classes\SimpleCache as SimpleCache;                        //SimpleCache class             >> To cache response ouput server side
use \classes\JSON as JSON;                                      //JSON class                    >> To handle JSON in better way (also for debug purpose)

    // Get module information (include cache)
    $app->get('/modules/first_mod/get/info/', function (Request $request, Response $response) {
        $fm = new FirstMod($this->db);
        $body = $response->getBody();
        $response = $this->cache->withEtag($response, $this->etag2hour.'-'.trim($_SERVER['REQUEST_URI'],'/'));
        if (SimpleCache::isCached(3600,["apikey"])){
            $datajson = SimpleCache::load(["apikey"]);
        } else {
            $datajson = SimpleCache::save($fm->viewInfo(),["apikey"]);
        }
        $body->write($datajson);
        return classes\Cors::modify($response,$body,200);
    })->add(new ApiKey);

    
    // Token Validation 
    $app->get('/modules/first_mod/check/token/{username}/{token}', function (Request $request, Response $response) {
        $fm = new FirstMod($this->db);
        $fm->username = $request->getAttribute('username');
        $fm->token = $request->getAttribute('token');
        $body = $response->getBody();
        $body->write($fm->checkToken());
        return classes\Cors::modify($response,$body,200);
    });