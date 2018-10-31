<?php
//**************************************************************************************
//**************************************************************************************
/**
 * Statement Core Class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Builders\SQL\Statements;

//**************************************************************************************
/**
 * Statement Core Class
 */
//**************************************************************************************
abstract class Core
{
    //=========================================================================
    // Traits
    //=========================================================================
    use \phpOpenFW\Builders\SQL\Traits\Aux;
    use \phpOpenFW\Builders\SQL\Traits\Condition;

    //=========================================================================
	// Class Memebers
    //=========================================================================
	protected $db_type = 'mysql';
	protected $bind_params = [];
	protected $depth = 0;

    //=========================================================================
    //=========================================================================
    // Set Database Type Method
    //=========================================================================
    //=========================================================================
    public function SetDbType($type)
    {
        if (!self::DbTypeIsValid($type)) {
            throw new \Exception("Invalid database type passed.");
        }
        $this->db_type = $type;
        return $this;
    }

    //=========================================================================
    //=========================================================================
    // Get Database Type Method
    //=========================================================================
    //=========================================================================
    public function GetDbType()
    {
        return $this->db_type;
    }

    //=========================================================================
    //=========================================================================
    // Get Bind Parameters Method
    //=========================================================================
    //=========================================================================
    public function &GetBindParams()
    {
		return $this->bind_params;
	}

    //=========================================================================
    //=========================================================================
    // Merge Bind Parameters Method
    //=========================================================================
    //=========================================================================
    public function MergeBindParams(Array $new_params)
    {
        //-----------------------------------------------------------------
        // Validate New Parameters
        //-----------------------------------------------------------------
        if (!$new_params) {
            return false;
        }
        $start_index = count($this->bind_params);

        //-----------------------------------------------------------------
        // MySQL
        //-----------------------------------------------------------------
		if ($this->db_type == 'mysql') {
    		if ($new_params <= 1) {
        		return false;
    		}
    		if ($start_index == 0) {
        		$this->bind_params[] = '';
    		}
    		foreach ($new_params as $np_index => $new_param) {
        		if ($np_index == 0) { continue; }
        		$np_index--;
        		$tmp_type = substr($new_params[0], $np_index, 1);
        		$this->bind_params[0] .= $tmp_type;
        		$this->bind_params[] = $new_param;
            }
		}
        //-----------------------------------------------------------------
        // Oracle
        //-----------------------------------------------------------------
		else if ($this->db_type == 'oracle') {
    		foreach ($new_params as $new_param) {
        		$new_index = ':p' . $start_index;
        		$this->bind_params[$new_index] = $new_param;
        		$start_index++;
    		}
		}

        //-----------------------------------------------------------------
        // Everything else. (PostgreSQL, SQL Server, etc.)
        //-----------------------------------------------------------------
		else {
    		$this->bind_params = array_merge($this->bind_params, $new_params);
		}
	}

    //=========================================================================
    //=========================================================================
    // To String Method
    //=========================================================================
    //=========================================================================
    public function __toString()
    {
		return $this->FormatWhere();
	}

}
