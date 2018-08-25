<?php
namespace Deepbajwa3\Instamojo;
use Illuminate\Support\ServiceProvider;
class InstamojoServiceProvider extends ServiceProvider{
    public function boot(){
        $this->publishes([
            __DIR__.'/config/instamojo.php' => config_path('instamojo.php'),
        ]);
    }
    public function register(){
    }
}
