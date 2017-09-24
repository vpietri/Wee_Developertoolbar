<?php

class Wee_DeveloperToolbar_Helper_Pro_Data extends Mage_Core_Helper_Abstract
{
	protected $_db;

	protected $_query_trace = array();

	protected $_md5_query_trace;

	const REGEX_BACKTRACE_CODE_ALL = '/app\/code\/([^\/]+)\/([^\/]+)\/([^\/]+)\/(.*).php\((\d+)\).*?::(.*)$/';

	const REGEX_BACKTRACE_CODE_LOCAL = '/app\/code\/(local|community)\/([^\/]+)\/([^\/]+)\/(.*).php\((\d+)\).*?::(.*)$/';

	const REGEX_BACKTRACE_TEMPLATE = '/app\/design\/(.*.phtml)\((\d+)\)\s(.*?::.*)$/';

	const REGEX_BACKTRACE_LIGHT = '/.*\((\d+)\).*?::(.*)$/';

	protected $_trace_sql_part;


	public function __construct()
	{
	    if (empty($this->_trace_sql_part)) {
        	$this->_trace_sql_part = Mage::getSingleton('wee_developertoolbar/pro_session')->getTraceSqlPart();
	    }
	}


	/**
	 *
	 * @param string $name
	 * @param string $testFile
	 * @param string $testType
	 */
	public function savePlan($name='Manual',$testFile='manual',$testType='manual')
	{
		$data= array('name' => $name,
    		        'pathfile' => $testFile,
    		        'type' => $testType,
		            'status' => 'running'
    );

		$this->getConnection()->insert('test_plan', $this->_getMagentoContext($data));

		return $this->getConnection()->lastInsertId();
	}


	/**
	 *
	 */
	public function loadExistingPlan()
	{
	    $select = $this->getConnection()->select()
	        ->from('test_plan', array('max(entity_id)'));

	    $mageContext = $this->_getMagentoContext();
	    foreach ($mageContext as $column=>$value) {
	        $select->where($column . '=?', $value);
	    }

	    $select->group(array_keys($mageContext));

	    return $this->getConnection()->fetchOne($select);
	}

	/**
	 *
	 * @param int $planId
	 */
	public function loadExistingRequest($planId, $queryTraceMd5)
	{
	    $select = $this->getConnection()->select()
	                ->from('test_request', array('entity_id'));

	    $requestContext = $this->_getRequestContext($planId, $queryTraceMd5);

	    foreach ($requestContext as $column=>$value) {
	        $select->where($column . '=?', $value);
	    }

	    $requestId = $this->getConnection()->fetchOne($select);

	    return $requestId;
	}

	/**
	 * Save SQL trace
	 *
	 */
	public function saveSqlTrace() {

		$request = Mage::app()->getRequest();

		$planId = $request->getParam('plan_id', false);
		if(empty($planId)) {
		    $planId = $this->loadExistingPlan();
		}

		if (!$planId) {
		    $planId = $this->savePlan();
		}


		$queryTrace = $this->getPreparedQueryTraces();
		$queryTraceMd5 = '';
		foreach ($queryTrace as $insertSql) {
		    $queryTraceMd5.=$insertSql['sql_md5sign'];
		}
		$queryTraceMd5 = count($queryTrace) . '::' . md5($queryTraceMd5);

		try {
		    if (!$planId) {
		        throw new Exception('Cannot set a plan to trace');
		    }

		    $idTestRequest = $this->loadExistingRequest($planId, $queryTraceMd5);
		    if(empty($idTestRequest)) {
		        $data = $this->_getRequestContext($planId, $queryTraceMd5);
		        $this->getConnection()->insert('test_request', $data);
		        $idTestRequest= $this->getConnection()->lastInsertId();
		    } else {
		        $idTestRequest = 0;
		    }

    		if (empty($idTestRequest)) {
    		    return false;
    		}

    		$sortOrder= 0;
    		foreach ($queryTrace as $insertSql) {

    		    $select = $this->getConnection()->select()
                		    ->from('test_trace_sql', array('entity_id'))
                		    ->where('sql_md5sign = ?',$insertSql['sql_md5sign']);




    		    $idSqlTrace = $this->getConnection()->fetchOne($select);

    		    if (!$idSqlTrace) {
    		        $this->getConnection()->insert('test_trace_sql', $insertSql);
    		        $idSqlTrace= $this->getConnection()->lastInsertId();
    		    }
    		    $sortOrder++;

    		    $insertSqlLink = array('request_id'=>$idTestRequest,
    		            'trace_id'=>$idSqlTrace,
    		            'sort_order'=>$sortOrder,
    		            'elapsed_time'=>$insertSql['elapsed_time']
    		    );

    		    $this->getConnection()->insert('test_request_trace_sql', $insertSqlLink);
    		}
		} catch (Exception $e) {
		    Mage::throwException($e->getMessage());
		}
	}


	public function getPreparedQueryTraces()
	{
        $sqlProfiler= Mage::getSingleton('core/resource')->getConnection('core_read')->getProfiler();

        $queryTrace = array();
        //On first Magento launch module config.xml is not read (only app/etc/config.xml)

        if(!empty($this->_trace_sql_part)) {
            $traceSqlPart = $this->_trace_sql_part;
        } else {
            $traceSqlPart = false;
        }

        if (get_class($sqlProfiler)=='Wee_DeveloperToolbar_Model_Pro_Db_Profiler') {
            foreach ($sqlProfiler->getQueryTraces() as $queryProfile) {
                $insertSql = array();

                $sqlQuery  = $this->_cleanSql($queryProfile['sql']->getQuery());

                $callCaptured = false;
                $phtmlMethod = '?';
                foreach ($queryProfile['trace'] as $stackLine) {

                    if (preg_match(self::REGEX_BACKTRACE_TEMPLATE,$stackLine, $matches)) {
                        $phtmlMethod = preg_replace('/.*::/', '', $matches[3]);
                    }

                    if ($callCaptured and preg_match(self::REGEX_BACKTRACE_CODE_ALL,$stackLine, $matches)) {
                        if($matches[6]!='include') {
                            $insertSql['class_method'] = $matches[6];
                        } else {
                            $insertSql['class_method'] = $phtmlMethod;
                        }
                        //Yes we we have found the caller
                        break;

                    } elseif (preg_match(self::REGEX_BACKTRACE_CODE_LOCAL,$stackLine, $matches)
                             or ($traceSqlPart and stripos($sqlQuery, $traceSqlPart) !==false and preg_match(self::REGEX_BACKTRACE_CODE_ALL,$stackLine, $matches))
                            )
                    {

                        if($traceSqlPart and stripos($sqlQuery, $traceSqlPart) !==false) {
                            $insertSql['trace_sql_part'] = $traceSqlPart;
                        }

                        $insertSql['code_pool'] = $matches[1];
                        $insertSql['module_vendor'] = $matches[2];
                        $insertSql['module_name'] = $matches[3];
                        $insertSql['class_path'] = $matches[4];
                        $insertSql['class_line'] = $matches[5];
                        //$insertSql['sql_query'] = $sqlQuery;

                        //Now we will try to check who is calling
                        $callCaptured = true;
                    }
                }

                $callstack = serialize($queryProfile['trace']);
                $insertSql['sql_query']     = $sqlQuery;
                $insertSql['elapsed_time']  = $queryProfile['sql']->getElapsedSecs();
                $insertSql['callstack']     = $callstack;
                $insertSql['sql_md5sign'] = md5($sqlQuery);
                $insertSql['stack_md5sign'] = md5($callstack);
                $queryTrace[] = $insertSql;
            }
        }

        return $queryTrace;
	}

	public function getTemplateTrace($stack)
	{
	    if(is_string($stack)) {
	        $stack = unserialize($stack);
	    }
	    foreach ($stack as $stackLine) {
    	    if (preg_match(self::REGEX_BACKTRACE_TEMPLATE,$stackLine, $matches)) {
    	        $trace = array();
    	        $trace['template'] = $matches[1];
    	        $trace['line'] = $matches[2];
    	        $trace['call'] = $matches[3];
    	        $trace['key'] = md5($stackLine);
    	        return $trace;
    	    }
	    }
	    return false;
	}

	public function getSimpleBackTrace($call, $fullBackTrace=false)
	{
	    $stack = $call['callstack'];
	    if(is_string($stack)) {
	        $stack = unserialize($stack);
	    }

	    if ($fullBackTrace) {
	        return implode ('<br/>', $stack);
	    } else {
    	    $simpleBacktrace = array();
    	    $i = 0;
    	    $lastLoop = false;
//     	    $start = false;
//     	    $keepTrace = '';
    	    foreach ($stack as $stackLine) {
    	        if (preg_match(self::REGEX_BACKTRACE_LIGHT,$stackLine, $matches)) {
    	            if($matches[2] == 'queryEnd') {
    	                continue;
    	            }

    	            if($matches[2] == $call['class_method']) {
    	                $lastLoop = true;

//     	                $start = true;
    	            }

//     	            if(!$start) {
//     	                continue;
//     	            }

    	            $keepTrace =  $matches[1];

    	            $i++;
    	            $simpleBacktrace[$i] = $matches[2];

    	            if($lastLoop) {
    	                break;
    	            }
    	        }
    	    }
    	    $simpleBacktrace[$i] .= '('.$keepTrace.')';


    	    return implode(' > ', array_reverse($simpleBacktrace));
	    }
	}





	/**
	 *
	 * @param string $sql
	 * @return string
	 */
	protected function _cleanSql($sql)
	{
	    $sql = $this->_cleanSqlDate($sql);

	    return $sql;
	}

	/**
	 *
	 * @param string $sql
	 * @return string
	 */
	protected function _cleanSqlDate($sql)
	{
	    $patterns = array ('/\'\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\'/',
	            '/\'\d{4}-\d{2}-\d{2}\'/',
	            '/(time [<>]) \d{10}/'
	            );

	    $replace = array ('\'_:DATETIME:_\'',
	            '\'_:DATE:_\'',
	            '$1 _:GETTIME:_'
	            );

	    $sql = preg_replace($patterns, $replace, $sql);

	    return $sql;
	}



	/**
	 * Get DB connection
	 *
	 * @return Zend_Db_Adapter_Pdo_Mysql
	 */
	public function getConnection()
	{
	    if (is_null($this->_db)) {
	        $this->_db = new Zend_Db_Adapter_Pdo_Mysql(array(
	                'host'     => 'localhost',
	                'username' => 'root',
	                'password' => '',
	                'dbname'   => 'mrsgto_bench'
	        ));
	    }

	    return $this->_db;
	}


	/**
	 *
	 * @param array $dataToMerge
	 */
	protected function _getMagentoContext(array $dataToMerge=array())
	{
	    $data= array('host' => Mage::app()->getRequest()->getHttpHost(),
	            'mage_version' => Mage::getVersion(),
	            'mage_package' => Mage::getDesign()->getPackageName(),
	            'mage_theme' => Mage::getDesign()->getTheme('template'),
	            'mage_store' => Mage::app()->getStore()->getCode(),
	            'mage_cache' => serialize(Mage::getResourceSingleton('core/cache')->getAllOptions())
	    );

	    return array_merge($data,$dataToMerge);
	}

	/**
	 *
	 * @param int $planId
	 */
	protected function _getRequestContext($planId, $queryTraceMd5)
	{
	    $request = Mage::app()->getRequest();

	    $data = array('plan_id'=>$planId,
	            'trace_md5sign'=> $queryTraceMd5,
	            'url'=>$request->getServer('REQUEST_URI'),
	            'request_url'=> Mage::helper('core/http')->getRequestUri(),
	            'post'=> ($request->getPost()) ? serialize($request->getPost()) : '',
	            'get'=> ($request->getQuery()) ? serialize($request->getQuery()) : ''
	    );

	    return $data;
	}

}
