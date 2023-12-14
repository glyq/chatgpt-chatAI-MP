<?php

namespace app\api\library\robot;


interface Channel{

    public function chat($param,$options);
    public function chatStream($param,$options,$connection='');

}
