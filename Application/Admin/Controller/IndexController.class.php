<?php
    namespace Admin\Controller;

    class IndexController extends BaseController{
        public function index(){
            $this->display();
            $this->main();
            $this->menu();
            $this->top();
        }
        public function main(){
            $this->display();
        }
        public function menu(){
            $this->display();
        }
        public function top(){
            $this->display();
        }
    }

?>