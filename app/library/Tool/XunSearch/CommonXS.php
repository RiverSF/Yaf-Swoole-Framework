<?php
namespace Library\Tool\XunSearch;

use XS;
use XSDocument;
use Library\Core\Log;

class CommonXS
{
    private static $xs = null;
    private static $configs = [];
    private static $columns = [];
    private static $XSDocument = null;

    /**
     * Notes:根据项目名称获取xs对象
     *
     * @param string $projectName 项目名称
     * @return object XS对象
     */
    public static function getXsObjByProjectName($projectName)
    {
        if (is_null(static::$xs) || !(static::$xs instanceof XS)) {
            $configFile = APPLICATION_PATH . '/conf/xunsearch/' . $projectName . '.ini';
            static::$xs[$projectName] = new XS($configFile);
            //读取配置文件获取设置的字段
            static::$configs = parse_ini_file($configFile, True);
            foreach (static::$configs as $key => $config) {
                if (is_array($config)) {
                    static::$columns[] = $key;
                }
            }
        }
        return static::$xs[$projectName];
    }

    /**
     * 获取配置的字段
     *
     * @param string $projectName 项目名称
     * @return array 配置的所有字段
     */
    public static function getXsFields($projectName)
    {
        static::getXsObjByProjectName($projectName);
        return ['code'=>200,'message'=>'','data'=>static::$columns];
    }

    /**
     * Notes:获取索引
     *
     * @param string $projectName 项目名称
     * @return array
     */
    public static function getIndex($projectName)
    {
        $xsObj = static::getXsObjByProjectName($projectName);
        return ['code'=>200,'message'=>'','data'=>$xsObj->index];
    }

    /**
     * Notes:新增文档
     *
     * @param string $projectName 项目名称
     * @param array $data 文档数据
     * @return array 新增文档成功
     */
    public static function addDocument($projectName, array $data)
    {
        try {
            $xsObj = static::getXsObjByProjectName($projectName);
            $doc = new XSDocument;
            $doc->setFields($data);
            $xsObj->index->add($doc);
            return ['code'=>200,'message'=>'','data'=>[]];
        } catch (\Exception $e) {
            \Log::error('Add-Document-Exception', ['message' => $e->getMessage()]);
            return ['code'=>999,'message'=>'','data'=>[]];
        }
    }

    /**
     * Notes:更新一条文档数据
     *
     * @param string $projectName 项目名称
     * @param array $data 需要更新的文档数据
     * @return array 更新文档结果
     */
    public static function updateDocument($projectName, $data)
    {
        try {
            $xsObj = static::getXsObjByProjectName($projectName);
            $index = $xsObj->index;
            //创建文件对象
            if(!(static::$XSDocument instanceof XSDocument)){
                static::$XSDocument = new XSDocument;
            }
            $doc = static::$XSDocument ;
            $doc->setFields($data);
            $index->update($doc);
            return ['code'=>200,'message'=>'','data'=>[]];
        } catch (\Exception $e) {
            Log::error('Update-Document-Exception', ['message' => $e->getMessage()]);
            return ['code'=>999,'message'=>'','data'=>[]];
        }
    }

    /**
     * Notes:删除一条文档数据
     *
     * @param string $projectName 项目名称
     * @param integer $id 主键id
     * @return array 删除文档结果
     */
    public static function delDocument($projectName, $id)
    {
        try {
            $xsObj = static::getXsObjByProjectName($projectName);
            $xsObj->index->del($id);
            return ['code'=>200,'message'=>'','data'=>[]];
        } catch (\Exception $e) {
            \Log::error('Del-Document-Exception', ['message' => $e->getMessage()]);
            return ['code'=>999,'message'=>'','data'=>[]];
        }
    }

    /**
     * Notes:搜索
     *
     * @param string $projectName 项目名称
     * @param string $query 搜索条件
     * @param integer $page 页码
     * @param integer $limit 限制条数
     * @param boolean $fuzzy 是否开启模糊搜索
     * @param array $sorts 排序方式
     * @param boolean $highlight 是否高亮匹配字段
     * @param array $highlightFields 高亮匹配字段
     * @return array 搜索结果数组
     */
    public static function search($projectName, $query, $page = 1, $limit = 10, $fuzzy = true, $sorts = [], $highlight = true, $highlightFields = [])
    {
        //获取xs对象
        $xsObj = static::getXsObjByProjectName($projectName);
        $data = [];
        //开始搜索
        try {
            $search = $xsObj->search;
            if ($fuzzy) {
                $search->setFuzzy();
            }
            $search->setQuery($query);
            if (!empty($sorts)) {
                $search->setMultiSort($sorts);
            }
            $search->setLimit($limit, ($page - 1) * $limit);
            $docs = $search->search(); // 执行搜索，将搜索结果文档保存在数组中

            //处理搜索结果
            $data['data'] = [];
            foreach ($docs as $key => $doc) {
                foreach (static::$columns as $column) {
                    if($highlight && array_search($column,$highlightFields) !== false){
                        $data['data'][$key][$column] = $search->highlight($doc->$column);
                    }else{
                        $data['data'][$key][$column] = $doc->$column;
                    }
                }
            }
            $searchCount = $search->count();

            $data['paginate'] = [
                "total" => $searchCount,
                "per_page" => $limit,
                "current_page" => $page,
                "last_page" => (int)ceil($searchCount / $limit)
            ];
            return ['code'=>200, 'message'=>'', 'data'=>$data];
        } catch (\Exception $e) {
            \Log::error("Xun-Search-Exception", ['data' => $query, "message" => $e->getMessage()]);
            return ['code'=>999, 'message'=>'', 'data'=>[]];
        }
    }

    /**
     * Notes:清空索引
     *
     * @param string $projectName 项目名称
     * @return array 清空索引结果
     */
    public static function cleanIndex($projectName)
    {
        try {
            $xsObj = static::getXsObjByProjectName($projectName);
            $xsObj->index->clean();
            return ['code'=>200,'message'=>'','data'=>[]];
        } catch (\Exception $e) {
            \Log::error('Xun-Clean-Index-Exception', ['message' => $e->getMessage()]);
            return ['code'=>999,'message'=>$e->getMessage(),'data'=>[]];
        }
    }

    /**
     * 获取最热搜索
     * @param $projectName
     * @param int $num （最大50条）
     * @return array|bool
     */
    public static function getHotQuery($projectName, $num = 10)
    {
        try{
            $xsObj = static::getXsObjByProjectName($projectName);
            $search = $xsObj->search;
            $words = $search->getHotQuery($num);
        }catch (\Exception $e){
            \Log::error("Xun-getHotQuery-Exception",["message"=>$e->getMessage()]);
            return ['code'=>200,'message'=>$e->getMessage(),'data'=>[]];
        }
        return ['code'=>200,'message'=>'','data'=>$words];
    }

}