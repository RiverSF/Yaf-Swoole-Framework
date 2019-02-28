<?php

namespace Library\Tool\XunSearch\Traits;

use Library\Core\Log;
use Library\Tool\XunSearch\CommonXS;

Trait XunSearchTrait
{
    /**
     * Notes:使用xunsearch搜索
     *
     * @param string $condition 搜索条件
     * @param string $projectName 项目名称
     * @param integer $page 页数
     * @param integer $page_size 单页数据数量
     * @return array 搜索结果
     */
    public function xunSearch($condition, $projectName = null, $page = 1, int $page_size = 10)
    {
        $projectName = $projectName ?? $this->XsProjectName;
        if (empty($projectName)) {
            return ['code'=>999,'message'=>'项目名不能为空','data'=>[]];
        }

        $result = CommonXS::search($projectName, $condition, $page, $page_size, true);

        if ($result['code'] == 200) {
            //xunsearch搜索匹配数量是预估值，不准确，通过查询最后一页数据来获取准确数量
            $lastPage = $result['data']['paginate']['last_page'];
            $lastInfo = CommonXS::search($projectName, $condition, $lastPage, $page_size);
            if (empty($lastInfo['data']['data'])) {
                $result['data']['paginate'] = $lastInfo['data']['paginate'];
            }
        }
        return $result;
    }

    /**
     * Notes:添加文档
     *
     * @param array $data 新增的文档数据
     * @param string $projectName 项目名称
     * @return array|boolean 新增文档的结果
     */
    public function addDocument($data, $projectName = null)
    {
        $projectName = $projectName ?? $this->XsProjectName;
        if (empty($projectName)) {
            return ['code'=>999,'message'=>'项目名不能为空','data'=>[]];
        }
        $result = CommonXS::addDocument($projectName, $data);
        return $result;
    }

    /**
     * Notes:修改文档
     *
     * @param array $data 修改的文档数据
     * @param string $projectName 项目名称
     * @return array|boolean 修改文档的结果
     */
    public function updateDocument($data, $projectName = null)
    {
        static $tryTimes = 1;
        $projectName = $projectName ?? $this->XsProjectName;
        if (empty($projectName)) {
            return ['code'=>999,'message'=>'项目名不能为空','data'=>[]];
        }

        $result = CommonXS::updateDocument($projectName, $data);
        if ($result['code'] != 200 && $tryTimes <= 3) {
            Log::error('updateDocumentError', ['result' => $result]);
            $tryTimes++;
            $result = $this->updateDocument($data, $projectName);
        } else {
            if($tryTimes > 1){
                Log::info('updateDocumentSuccess', ['result' => $result]);
            }
            $tryTimes = 1;
        }
        return $result;
    }

    /**
     * Notes:删除文档
     *
     * @param integer $id 文档主键id
     * @param string $projectName 项目名称
     * @return array 删除文档的结果
     */
    public function delDocument($id, $projectName = null)
    {
        $projectName = $projectName ?? $this->XsProjectName;
        if (empty($projectName)) {
            return ['code'=>999,'message'=>'项目名不能为空','data'=>[]];
        }
        $result = CommonXS::delDocument($projectName, $id);
        return $result;
    }

    /**
     * Note:获取所有索引
     *
     * @param string $projectName 项目名称
     * @return array 获取索引的结果
     */
    public function getIndex($projectName = null)
    {
        $projectName = $projectName ?? $this->XsProjectName;
        if (empty($projectName)) {
            return ['code'=>999,'message'=>'项目名不能为空','data'=>[]];
        }
        $result = CommonXS::getIndex($projectName);
        return $result;
    }

    /**
     * Notes:清空索引
     *
     * @param string $projectName 项目名称
     * @return array 清空索引的结果
     */
    public function cleanIndex($projectName = null)
    {
        $projectName = $projectName ?? $this->XsProjectName;
        if (empty($projectName)) {
            return ['code'=>999,'message'=>'项目名不能为空','data'=>[]];
        }
        $result = CommonXS::cleanIndex($projectName);
        return $result;
    }

    /**
     * Notes:获取配置的字段
     *
     * @param string $projectName 项目名称
     * @return array 配置的字段
     */
    public function getXsFields($projectName = null)
    {
        $projectName = $projectName ?? $this->XsProjectName;
        if (empty($projectName)) {
            return ['code'=>999,'message'=>'项目名不能为空','data'=>[]];
        }
        $result = CommonXS::getXsFields($projectName);
        return $result;
    }

    /**
     * Notes:获取配置的字段
     *
     * @param string $projectName 项目名称
     * @param int $num
     * @return array 配置的字段
     */
    public function getHotSearchWords($projectName = null, $num = 10)
    {
        $projectName = $projectName ?? $this->XsProjectName;
        if (empty($projectName)) {
            return ['code'=>999,'message'=>'项目名不能为空','data'=>[]];
        }
        $result = CommonXS::getHotQuery($num, $projectName);
        return $result;
    }
}