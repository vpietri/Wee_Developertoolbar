<?php


class Wee_DeveloperToolbar_Helper_Pro_Analyzer extends Mage_Core_Helper_Abstract
{
    protected $_agg_called_queries;

    protected $_agg_module_stat;

    protected $_total_numer=0;

    protected $_total_number_auto=0;




    protected function _aggregateCalls()
    {
        if (is_null($this->_agg_called_queries)) {
            $calledQueries = Mage::helper('wee_developertoolbar/pro_data')->getPreparedQueryTraces();

            $this->_agg_called_queries = array();
            $this->_agg_module_stat = array();
            foreach ($calledQueries as $call) {
                if (!empty($call['module_vendor'])) {

                    if ($call['module_vendor']=='Mrsgto' or $call['module_vendor']=='Wee') {
                        $this->_total_number_auto++;
                        continue;
                    }

                    if (!empty($call['trace_sql_part'])) {
                        $fullBackTrace = true;
                    } else {
                        $fullBackTrace = false;
                    }

                    $callAggKey = $call['code_pool'] . '::' .
                            $call['module_vendor'] . '::' .
                            $call['module_name'];

                    if (empty($this->_agg_module_stat[$callAggKey])) {
                        $this->_agg_module_stat[$callAggKey] = $call;
                        $this->_agg_module_stat[$callAggKey]['number'] = 0;
                    } else {
                        $this->_agg_module_stat[$callAggKey]['elapsed_time'] += $call['elapsed_time'];
                    }
                    $this->_agg_module_stat[$callAggKey]['number']++;


                    //Define a common key
                    $callAggKey .= '::' .$call['class_path'] . '::' .
                            $call['class_method'] . '::' .
                            $call['class_line'];

                    $simplebackTrace = Mage::helper('wee_developertoolbar/pro_data')->getSimpleBackTrace($call, $fullBackTrace);

                    $templateTrace = Mage::helper('wee_developertoolbar/pro_data')->getTemplateTrace($call['callstack']);

                    if (empty($this->_agg_called_queries[$callAggKey])) {
                        if ($templateTrace) {
                            $templateTrace['number']=1;
                            $call['template_path'][$templateTrace['key']] = $templateTrace;
                        }
                        $call['simple_traces'] = array($simplebackTrace=>1);
                        $call['number'] = 0;
                        $call['sql_query']= array($call['sql_md5sign'] => $call['sql_query']);
                        $this->_agg_called_queries[$callAggKey] = $call;
                    } else {
                        $this->_agg_called_queries[$callAggKey]['elapsed_time'] += $call['elapsed_time'];
                        if(empty($this->_agg_called_queries[$callAggKey]['simple_traces'][$simplebackTrace])) {
                            $this->_agg_called_queries[$callAggKey]['simple_traces'][$simplebackTrace] = 1;
                        } else {
                            $this->_agg_called_queries[$callAggKey]['simple_traces'][$simplebackTrace]++;
                        }

                        $this->_agg_called_queries[$callAggKey]['sql_query'][$call['sql_md5sign']] = $call['sql_query'];

                        if ($templateTrace) {
                            if(!isset($this->_agg_called_queries[$callAggKey]['template_path'][$templateTrace['key']])) {
                                $templateTrace['number']=1;
                                $this->_agg_called_queries[$callAggKey]['template_path'][$templateTrace['key']] = $templateTrace;
                            } else {
                                $this->_agg_called_queries[$callAggKey]['template_path'][$templateTrace['key']]['number']++;
                            }
                        }
                    }

                    $this->_agg_called_queries[$callAggKey]['stack_call'][$call['stack_md5sign']] = $call['stack_md5sign'];
                    $this->_agg_called_queries[$callAggKey]['number']++;
                    $this->_total_numer++;
                }
            }

            if (!empty($this->_agg_module_stat)) {
                uasort($this->_agg_module_stat, array($this,'_sortByNumber'));
            }
            if (!empty($this->_agg_called_queries)) {
                uasort($this->_agg_called_queries, array($this,'_sortByNumber'));
            }
        }
    }


    public function getSuspectedCalls()
    {
        $this->_aggregateCalls();

        return $this->_agg_called_queries;
    }

    public function getStatisticsCalls()
    {
        $this->_aggregateCalls();

        return $this->_agg_module_stat;
    }

    protected function _sortByNumber($a,$b)
    {
        if($a['number']<$b['number']) {
            return 1;
        } elseif($a['number']>$b['number']) {
            return -1;
        } else {
            return 0;
        }
    }

    public function getLabelTrace()
    {
        $this->getSuspectedCalls();

        return  $this->_total_numer . ' by stack ('.$this->_total_number_auto.')';
    }
}
