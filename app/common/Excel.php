<?php
/**
 * User: River
 * Date: 2019/2/26
 * Time: 18:10
 */
namespace Common;

class Excel
{
    /**
     * @param string $path      读取文件路径
     * @param array $map        列字段映射 ['A'=>'name']
     * @param int $firstRow     从第几行开始读取数据
     * @param bool $row         是否以行展示(默认以列展示)
     * @return array|string
     */
    public function readExcel($path, $map, $firstRow = 2, $row = true)
    {
        try {
            $data = [];

            $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($path);
            $currentSheet = $objPHPExcel->getSheet(0);
            $allColumn = $currentSheet->getHighestColumn();
            $allRow = $currentSheet->getHighestRow();

            for ($currentRow = $firstRow; $currentRow <= $allRow; $currentRow++) {
                for ($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65, $currentRow)->getValue();
                    if ($row) {
                        if (isset($map[$currentColumn])) {
                            $data[$currentRow][$map[$currentColumn]] = trim($val);
                        }
                    } else {
                        if (isset($map[$currentColumn])) {
                            $data[$map[$currentColumn]][$currentRow] = trim($val);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('PHPEXCEL-EXCEPTION', [$e->getMessage()]);
            return 'Excel 文件存在异常，请新建文件重新上传操作';
        }

        return $data;
    }

    /**
     * Author; River
     * Notes:  excel下载
     * Date: 2018/11/16
     * @param $title /第一行的标题
     * @param $data /导出数据
     * @param string $filename /文件名称
     * @param string $type /row 横向填入数据-   column 纵向填入 |
     * @param string $file_path
     * @return array
     */
    function createExcel($title, $data, $filename = 'normal', $type = 'row', $file_path = APPLICATION_PATH . '/storage/downloads/')
    {
        try {
            $excel = new \PHPExcel();
            $act_sheet = $excel->setActiveSheetIndex(0);
            $act_sheet->getColumnDimension()->setAutoSize(true);
            $column_count = count($title);
            for ($i = 0; $i < $column_count; $i++) {
                $act_sheet->setCellValueByColumnAndRow($i, 1, $title[$i]);
            }

            switch ($type) {
                case 'row':
                    $row = 2;
                    foreach ($data as $v) {
                        $column = 0;
                        foreach ($v as $key => $value) {
                            $act_sheet->setCellValueByColumnAndRow($column, $row, $value);
                            $column++;
                        }
                        $row++;
                    }
                    break;
                case 'column':
                    $column = 0;
                    foreach ($data as $v) {
                        $row = 2;
                        foreach ($v as $key => $value) {
                            $act_sheet->setCellValueByColumnAndRow($column, $row, $value);
                            $row++;
                        }
                        $column++;
                    }
                    break;
                default :
                    break;
            }
//        $filename = iconv('utf-8', 'gb2312', $filename);//文件名称
//        header('pragma:public');
//        header("Content-type:application/vnd.ms-excel;charset=utf-8;name= $xlsTitle.xls");
//        header("Content-Disposition:attachment;filename = $filename.xls");//attachment新窗口打印inline本窗口打印
            $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
//        $objWriter->save('php://input');
            $objWriter->save($file_path . $filename . '.xls');
        } catch (\Exception $e) {
            \Log::error('Create-Excel-File-Exception', ['errMsg'=>$e->getMessage()]);
            return ['code'=>999, 'message'=>$e->getMessage()];
        }
        return ['code'=>200];
    }
}