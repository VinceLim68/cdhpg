<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace think\db\connector;
use PDO;
use think\db\Connection;
/**
 * Sqlsrv1数据库驱动
 */
class Sqlsrv1 extends Connection
{
    // PDO连接参数
    protected $params = [
        PDO::ATTR_CASE              => PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ];
    /**
     * 解析pdo连接的dsn信息
     * @access protected
     * @param array $config 连接信息
     * @return string
     */
    protected function parseDsn($config)
    {
        $dsn = 'sqlsrv:Database=' . $config['database'] . ';Server=' . $config['hostname'];
        if (!empty($config['hostport'])) {
            $dsn .= ',' . $config['hostport'];
        }
        return $dsn;
    }
    /**
     * 取得数据表的字段信息
     * @access public
     * @param string $tableName
     * @return array
     */
    public function getFields($tableName)
    {
        $this->initConnect(true);
        list($tableName) = explode(' ', $tableName);
        $sql             = "SELECT c.name,c.system_type_id,d.definition,c.is_nullable
                            FROM sys.columns c 
                            LEFT JOIN sys.default_constraints d 
                            ON c.default_object_id = d.object_id
                            WHERE c.object_id = OBJECT_ID('$tableName')
                            ORDER BY c.column_id";
        $pdo    = $this->linkID->query($sql);
        $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $info   = [];
        if ($result) {
            foreach ($result as $key => $val) {
                $val                       = array_change_key_case($val);
                $info[$val['name']] = [
                    'name'    => $val['name'],
                    'type'    => $val['system_type_id'],
                    'notnull' => (bool) ('' === $val['is_nullable']), // not null is empty, null is yes
                    'default' => $val['definition'],
                    'primary' => false,
                    'autoinc' => false,
                ];
            }
        }
        return $this->fieldCase($info);
    }
    /**
     * 取得数据表的字段信息
     * @access public
     * @param string $dbName
     * @return array
     */
    public function getTables($dbName = '')
    {
        $sql = "SELECT name FROM sys.all_objects 
                WHERE type_desc = 'USER_TABLE'";
        $pdo    = $this->linkID->query($sql);
        $result = $pdo->fetchAll(PDO::FETCH_ASSOC);
        $info   = [];
        foreach ($result as $key => $val) {
            $info[$key] = current($val);
        }
        return $info;
    }
    /**
     * SQL性能分析
     * @access protected
     * @param string $sql
     * @return array
     */
    protected function getExplain($sql)
    {
        return [];
    }
}

