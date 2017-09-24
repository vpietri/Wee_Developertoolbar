<?php

class Wee_DeveloperToolbar_Model_Pro_Db_Profiler extends Zend_Db_Profiler
{
    /**
     * Array of traces for each query.
     *
     * @var array
     */
    protected $_queryTraces = array();

    /**
     * Enable or disable the profiler.  If $enable is false, the profiler
     * is disabled and will not log any queries sent to it.
     *
     * @param  boolean $enable
     * @return Zend_Db_Profiler Provides a fluent interface
     */
    public function setEnabled($enable)
    {

        if (!isset($_SERVER['REQUEST_METHOD'])) {
            $this->_enabled = false;
        } else {
            $this->_enabled = (boolean) $enable;
        }

        return $this;
    }


    /**
     * Starts a query.  Creates a new query profile object (Zend_Db_Profiler_Query)
     * and returns the "query profiler handle".  Run the query, then call
     * queryEnd() and pass it this handle to make the query as ended and
     * record the time.  If the profiler is not enabled, this takes no
     * action and immediately returns null.
     *
     * @param  string  $queryText   SQL statement
     * @param  integer $queryType   OPTIONAL Type of query, one of the Zend_Db_Profiler::* constants
     * @return integer|null
     */
    public function queryStart($queryText, $queryType = null)
    {
        if (!$this->_enabled) {
            return null;
        }

        // make sure we have a query type
        if (null === $queryType) {
            switch (strtolower(substr(ltrim($queryText), 0, 6))) {
                case 'insert':
                    $queryType = self::INSERT;
                    break;
                case 'update':
                    $queryType = self::UPDATE;
                    break;
                case 'delete':
                    $queryType = self::DELETE;
                    break;
                case 'select':
                    $queryType = self::SELECT;
                    break;
                default:
                    $queryType = self::QUERY;
                    break;
            }
        }

		Varien_Profiler::start($queryText, 'db');


// 		if(strpos($queryText,'organizer_task')!==false) {
//     		var_dump($queryText);
//     		$debug= debug_backtrace (false);
//     		foreach($debug as $trace)
//     		    var_dump((isset($trace['file'])?$trace['file']:'?????').'('.$trace['function'].') line:'.(isset($trace['line'])?$trace['line']:'????'));
//     		exit;

// 		}
        /**
         * @see Zend_Db_Profiler_Query
         */
        #require_once 'Zend/Db/Profiler/Query.php';
        $this->_queryProfiles[] = new Zend_Db_Profiler_Query($queryText, $queryType);

        end($this->_queryProfiles);

        return key($this->_queryProfiles);
    }

    /**
     * Ends a query.  Pass it the handle that was returned by queryStart().
     * This will mark the query as ended and save the time.
     *
     * @param  integer $queryId
     * @throws Zend_Db_Profiler_Exception
     * @return void
     */
    public function queryEnd($queryId)
    {
        // Don't do anything if the Zend_Db_Profiler is not enabled.
        if (!$this->_enabled) {
            return self::IGNORED;
        }

        // Check for a valid query handle.
        if (!isset($this->_queryProfiles[$queryId])) {
            /**
             * @see Zend_Db_Profiler_Exception
             */
            #require_once 'Zend/Db/Profiler/Exception.php';
            throw new Zend_Db_Profiler_Exception("Profiler has no query with handle '$queryId'.");
        }

        $qp = $this->_queryProfiles[$queryId]; /* @var $qp Zend_Db_Profiler_Query */

        // Ensure that the query profile has not already ended
        if ($qp->hasEnded()) {
            /**
             * @see Zend_Db_Profiler_Exception
             */
            #require_once 'Zend/Db/Profiler/Exception.php';
            throw new Zend_Db_Profiler_Exception("Query with profiler handle '$queryId' has already ended.");
        }

        // End the query profile so that the elapsed time can be calculated.
        $qp->end();

		$this->_queryTraces[$queryId] = $this->getSimpledBacktrace();

		Varien_Profiler::stop($qp->getQuery());

        /**
         * If filtering by elapsed time is enabled, only keep the profile if
         * it ran for the minimum time.
         */
        if (null !== $this->_filterElapsedSecs && $qp->getElapsedSecs() < $this->_filterElapsedSecs) {
            unset($this->_queryProfiles[$queryId]);
            return self::IGNORED;
        }

        /**
         * If filtering by query type is enabled, only keep the query if
         * it was one of the allowed types.
         */
        if (null !== $this->_filterTypes && !($qp->getQueryType() & $this->_filterTypes)) {
            unset($this->_queryProfiles[$queryId]);
            return self::IGNORED;
        }

        return self::STORED;
    }

	/**
	 * Get an array of query with their own traces.
	 *
	 * @return array
	 */
	public function getQueryTraces()
	{

		$traces= array();
		foreach ($this->_queryProfiles as $queryId=>$queryProfile) {
			$traces[$queryId] = array('sql'=>$queryProfile,
					                  'trace'=> isset($this->_queryTraces[$queryId]) ? $this->_queryTraces[$queryId] : array() );
		}


		return $traces;
	}


	/**
	 * Get a simple back trace
	 *
	 * @return array
	 */
	protected function getSimpledBacktrace()
	{
	    $d = debug_backtrace();
	    array_shift($d);
	    $out = array();
	    foreach ($d as &$f) {
	        if (!isset($f['file'])) {
	            $f['file'] = '_NOFILE_';
	            $f['relative_path'] = '_NOFILE_';
	        } else {
    	        $f['relative_path'] = str_replace(BP . DS, '', $f['file']);
	        }
	        (!isset($f['line'])) && $f['line'] = '_NOLINE_';
	        (!isset($f['class'])) && $f['class'] = '_NOCLASS_';
	    }
	    foreach ($d as $i => $f) {
	        $out[] = sprintf("%s(%s) %s::%s",
	                $f['relative_path'],
	                $f['line'],
	                $f['class'],
	                $f['function']
	                );
	    }
	    return $out;
	}

}


