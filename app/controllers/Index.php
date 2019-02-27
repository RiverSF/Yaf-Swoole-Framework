<?php

use Library\Core\Controller;
use Library\Core\Model;
use Crada\Apidoc\Builder;
use Crada\Apidoc\Exception;

class IndexController extends Controller
{
    /**
     * @ApiDescription(section="Index-填写文件名", description="财务应付系统首页")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/index：接口地址")
     */
    public function indexAction()
    {
        echo 'Welcome Finance';
    }

    /**
     * @ApiDescription(section="Index", description="很重要：每次更新文档注释后必须执行此方法，用以添加新的文档内容")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/index/apidoc")
     */
    public function apiDocAction()
    {
        /**
         *  白名单：是否允许显示到文档中，手动配置
         */
        $classes = [
            'IndexController',
        ];

        $output_dir  = APPLICATION_PATH.'/app/views/index';
        $output_file = 'api.phtml'; // defaults to index.html

        try {
            $builder = new Builder($classes, $output_dir, '在线接口文档', $output_file);
            $builder->generate();
            $this->json(200, 'Generate document success');
        } catch (Exception $e) {
            echo 'There was an error generating the documentation: ', $e->getMessage();
        }
    }

    /**
     * @ApiDescription(section="Index", description="查看文档地址")
     * @ApiParams(name="id", type="number", nullable=false, description="供应商ID", sample="12")
     * @ApiMethod(type="get")
     * @ApiRoute(name="/index/document")
     */
    public function documentAction()
    {
        $this->display('api');
    }
}
