<?php
//**************************************************************************************
//**************************************************************************************
/**
 * SQL Nested Conditions Class
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
 * SQL Nested Conditions Class
 */
//**************************************************************************************
class NestedConditions extends Core
{
    //=========================================================================
    // Traits
    //=========================================================================
    use Traits\Where;

    //=========================================================================
	// Class Memebers
    //=========================================================================
	protected $parent_query;

    //=========================================================================
    //=========================================================================
    // Constructor Method
    //=========================================================================
    //=========================================================================
    public function __construct($parent_query, $depth)
    {
        if (gettype($parent_query) != 'object') {
            throw new \Exception("Parent query must be passed to nested conditions object.");
        }
        $this->parent_query = $parent_query;
        $this->db_type = $parent_query->GetDbType();
        $this->depth = $depth + 1;
        $this->bind_params = &$this->parent_query->GetBindParams();
    }


}
