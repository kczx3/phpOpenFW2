<?php
//**************************************************************************************
//**************************************************************************************
/**
 * SQL Select Class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Builders\SQL;

//**************************************************************************************
/**
 * SQL Select Class
 */
//**************************************************************************************
class Select extends Core
{

    //=========================================================================
	// Class Memebers
    //=========================================================================
    protected $sql_type = 'select';
	protected $group_by = [];
	protected $order_by = [];
	protected $having = [];
	protected $limit = false;

    //=========================================================================
    //=========================================================================
    // Constructor Method
    //=========================================================================
    //=========================================================================
    public function __construct($db_type=false)
    {
	    $this->sql_type = 'select';
	    if ($db_type) { $this->db_type = $db_type; }
	}

    //=========================================================================
    //=========================================================================
    // Get Method
    //=========================================================================
    //=========================================================================
    public function GetSQL()
    {
		//-------------------------------------------------------
		// Select Fields / From Tables...
		//-------------------------------------------------------
		$strsql = $this->FormatFields() . "\n";
		$strsql .= $this->FormatFrom() . "\n";

		//-------------------------------------------------------
		// Where
		//-------------------------------------------------------
		$strsql .= $this->FormatWhere() . "\n";

		//-------------------------------------------------------
		// Group By / Order By
		//-------------------------------------------------------
		$strsql .= $this->FormatGroupBy() . "\n";
		$strsql .= $this->FormatOrderBy() . "\n";

		//-------------------------------------------------------
		// Having
		//-------------------------------------------------------
		$strsql .= $this->FormatHaving() . "\n";

		//-------------------------------------------------------
		// Limit
		//-------------------------------------------------------
		if ($this->limit && $this->db_type == 'mysql') {
			$strsql .= ' LIMIT ' . (int)$this->limit;
		}

		//-------------------------------------------------------
		// Return SQL
		//-------------------------------------------------------
		return $strsql;
	}

    //#####################################################################################
    //#####################################################################################
    // Query Parameter Setting Methods
    //#####################################################################################
    //#####################################################################################

    //=========================================================================
    //=========================================================================
	// Add Group By Method
    //=========================================================================
    //=========================================================================
	public function GroupBy($group_by)
	{
    	return $this->AddItem($this->group_by, $group_by);
	}

    //=========================================================================
    //=========================================================================
	// Add Order By Method
    //=========================================================================
    //=========================================================================
	public function OrderBy($order_by)
	{
    	return $this->AddItem($this->order_by, $order_by);
	}

    //=========================================================================
    //=========================================================================
	// Limit Method
    //=========================================================================
    //=========================================================================
	public function Limit($limit)
	{
		if (is_null($limit)) {
			$this->limit = false;
		}
		else if ($limit != '') {
    		$this->limit = $limit;
		}
		return $this;
	}

    //=========================================================================
    //=========================================================================
	// Having Clause Method
    //=========================================================================
    //=========================================================================
	public function Having($condition)
	{

	}

    //#####################################################################################
    //#####################################################################################
    // Protected Methods
    //#####################################################################################
    //#####################################################################################

    //=========================================================================
    //=========================================================================
    // Format Fields Method
    //=========================================================================
    //=========================================================================
    protected function FormatFields()
    {
		return 'SELECT ' . implode(', ', $this->fields);
    }

    //=========================================================================
    //=========================================================================
    // Format From Method
    //=========================================================================
    //=========================================================================
    protected function FormatFrom()
    {
		return 'FROM ' . implode(', ', $this->from);
    }

    //=========================================================================
    //=========================================================================
    // Render Group By Method
    //=========================================================================
    //=========================================================================
    protected function FormatGroupBy()
    {
	    $group_by = $this->group_by;
		if (is_array($group_by)) {
			$group_by = implode(', ', $group_by);
		}
        if ($group_by == '') { return false; }
		$group_by = 'GROUP BY ' . $group_by;
		return $group_by;
	}

    //=========================================================================
    //=========================================================================
    // Format Order By Method
    //=========================================================================
    //=========================================================================
    protected function FormatOrderBy()
    {
	    $order_by = $this->order_by;
		if (is_array($order_by)) {
			$order_by = implode(', ', $order_by);
		}
        if ($order_by == '') { return false; }
		$order_by = 'ORDER BY ' . $order_by;
		return $order_by;
	}

    //=========================================================================
    //=========================================================================
	// Format Having Clause Method
    //=========================================================================
    //=========================================================================
	protected function FormatHaving()
	{

	}

}
