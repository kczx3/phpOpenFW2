<?php
//**************************************************************************************
//**************************************************************************************
/**
 * SQL Condition Class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Builders\SQL\Conditions;
use \Closure;

//**************************************************************************************
/**
 * SQL Condition Class
 */
//**************************************************************************************
class Condition extends \phpOpenFW\Builders\SQL\Core
{
    //==================================================================================
	// Class Memebers
    //==================================================================================
	protected $parent_query;
	protected $field = false;
	protected $op = false;
	protected $val = false;
	protected $type = 's';

    //==================================================================================
    //==================================================================================
    // Constructor Method
    //==================================================================================
    //==================================================================================
    public function __construct($parent_query, $depth, $field, $op, $val, $type)
    {
        //------------------------------------------------------------------------------
        // Validate Parameters
        //------------------------------------------------------------------------------
        if (gettype($parent_query) != 'object') {
            throw new \Exception('Parent query must be passed to nested conditions object.');
        }
        if (!$field) {
            $lower_op = trim(strtolower($op));
            $no_field_allowed = ['exists', 'not exists'];
            if (!in_array($lower_op, $no_field_allowed)) {
                throw new \Exception('Invalid field name given.');
            }
        }
        if (gettype($field) != 'object' && !self::IsValidOperator($op)) {
            throw new \Exception('Invalid operator given.');
        }

        //------------------------------------------------------------------------------
        // Set Class Variables
        //------------------------------------------------------------------------------
        $this->parent_query = $parent_query;
        $this->db_type = $parent_query->GetDbType();
        $this->depth = $depth + 1;
        $this->bind_params = &$this->parent_query->GetBindParams();
        $this->field = $field;
        $this->op = $op;
        $this->val = $val;
        $this->type = $type;
    }

    //==================================================================================
    //==================================================================================
    // Get Instance Method
    //==================================================================================
    //==================================================================================
    public static function Instance($parent_query, $depth, $field, $op, $val, $type)
    {
        return new static($parent_query, $depth, $field, $op, $val, $type);
    }

    //==================================================================================
    //==================================================================================
    // To String Method
    //==================================================================================
    //==================================================================================
    public function __toString()
    {
		return $this->GetSQL();
	}

    //==================================================================================
    //==================================================================================
    // Get SQL Method
    //==================================================================================
    //==================================================================================
    public function GetSQL()
    {
        //------------------------------------------------------------------------------
        // Nested Conditions
        //------------------------------------------------------------------------------
        if ($this->field instanceof Closure) {
        	$nested = new Nested($this, $this->depth - 1);
        	($this->field)($nested);
        	$rear_pad = str_repeat(' ', $this->depth * 2);
        	$nested = (string)$nested;
        	if ($nested) {
            	return "({$nested}\n{$rear_pad})";
            }
            return '';
        }
        //------------------------------------------------------------------------------
        // Sub-query Condition
        //------------------------------------------------------------------------------
        else if (gettype($this->val) == 'object' && get_class($this->val) == 'phpOpenFW\Builders\SQL\Select') {
            $this->val->SetParentQuery($this);
            $sql = $this->val->GetSQL();
            $this->bind_params = $this->val->GetBindParams();
            if ($sql) {

                //----------------------------------------------------------------------
                // Adjust indentation to be correct for current depth
                //----------------------------------------------------------------------
                $front_pad = str_repeat(' ', ($this->depth * 2) + 2);
                $front_pad2 = str_repeat(' ', (($this->depth - 1) * 2) + 2);
                $sql = str_ireplace("\n", "\n{$front_pad}", $sql);
                $sql = \phpOpenFW\Format\Strings::str_ireplace_last("\n{$front_pad}", "\n{$front_pad2}", $sql);

                //----------------------------------------------------------------------
                // Build Sub-query String
                //----------------------------------------------------------------------
                $ret_str = '';
                if ($this->field) {
                    $ret_str .= $this->field;
                }
                if ($this->op) {
                    $ret_str .= ' ' . $this->op;
                }
                if ($ret_str) {
                    $ret_str .= ' ';
                }
                $ret_str .= "(\n{$front_pad}{$sql})";
                return $ret_str;
            }
        }
        //------------------------------------------------------------------------------
        // Single / Scalar Conditions
        //------------------------------------------------------------------------------
        else {

            //--------------------------------------------------------------------------
            // Lowercase the Operator
            //--------------------------------------------------------------------------
            $lc_op = strtolower($this->op);
    
            //--------------------------------------------------------------------------
            // Switch based on Operator
            //--------------------------------------------------------------------------
            switch ($lc_op) {
    
                //----------------------------------------------------------------------
                // In / Not In (Multiple Value Conditions)
                //----------------------------------------------------------------------
                case 'in':
                case 'not in':
                    return $this->MultipleValueCondition();
                    break;
    
                //----------------------------------------------------------------------
                // Between / Not Between
                //----------------------------------------------------------------------
                case 'between':
                case 'not between':
                    return $this->BetweenCondition();
                    break;
    
                //----------------------------------------------------------------------
                // Is Null
                //----------------------------------------------------------------------
                case 'is null':
                    return $this->IsNullCondition();
                    break;
    
                //----------------------------------------------------------------------
                // Is Not Null
                //----------------------------------------------------------------------
                case 'is not null':
                    return $this->IsNotNullCondition();
                    break;
    
                //----------------------------------------------------------------------
                // Everything else (Single Value Conditions)
                //----------------------------------------------------------------------
                default:
                    return $this->SingleValueCondition();
                    break;
            }
        }

        //------------------------------------------------------------------------------
        // Invalid Condition
        //------------------------------------------------------------------------------
        throw new \Exception('Invalid condition.');
    }

    //##################################################################################
    //##################################################################################
    // Condition Formatting Methods
    //##################################################################################
    //##################################################################################

    //==================================================================================
    //==================================================================================
    // Is Null
    //==================================================================================
    //==================================================================================
    protected function IsNullCondition()
    {
        return "{$this->field} IS NULL";
    }

    //==================================================================================
    //==================================================================================
    // Is NOT Null
    //==================================================================================
    //==================================================================================
    protected function IsNotNullCondition(String $field)
    {
        return "{$this->field} IS NOT NULL";
    }

    //==================================================================================
    //==================================================================================
    // Single Value Condition
    //==================================================================================
    // (=, !=, <>, <, <=, >, >=, like, not like)
    //==================================================================================
    //==================================================================================
    protected function SingleValueCondition()
    {
        //------------------------------------------------------------------------------
        // False Value? Return.
        //------------------------------------------------------------------------------
        if ($this->val === false) {
            return false;
        }
        //------------------------------------------------------------------------------
        // Null Value
        //------------------------------------------------------------------------------
        else if (is_null($this->val)) {
            if ($this->op == '=') {
                return $this->IsNullCondition($this->field);
            }
            else if ($this->op == '!=') {
                return $this->IsNotNullCondition($this->field);
            }
            else {
                return false;
            }
        }
        if (!is_scalar($this->val)) {
            throw new \Exception('Value must be a scalar value.');
        }

        //------------------------------------------------------------------------------
        // Add Bind Parameter
        //------------------------------------------------------------------------------
        $place_holder = $this->AddBindParam($this->val, $this->type);

        //------------------------------------------------------------------------------
        // Create and Return Condition
        //------------------------------------------------------------------------------
        return "{$this->field} {$this->op} {$place_holder}";
    }

    //==================================================================================
    //==================================================================================
    // Multiple Value Condition
    //==================================================================================
    // (in, not in)
    //==================================================================================
    //==================================================================================
    protected function MultipleValueCondition()
    {
        //------------------------------------------------------------------------------
        // No Values? Return.
        //------------------------------------------------------------------------------
        if (!$this->val) {
            return false;
        }

        //------------------------------------------------------------------------------
        // Add Bind Parameters
        //------------------------------------------------------------------------------
        $place_holders = $this->AddBindParams($this->val, $this->type);

        //------------------------------------------------------------------------------
        // Create and Return Condition
        //------------------------------------------------------------------------------
        return "{$this->field} {$this->op} ({$place_holders})";
    }

    //==================================================================================
    //==================================================================================
    // Between Condition
    //==================================================================================
    // (between, not between)
    //==================================================================================
    //==================================================================================
    protected function BetweenCondition()
    {
        //------------------------------------------------------------------------------
        // No Values? Return.
        //------------------------------------------------------------------------------
        if (!$this->val) {
            return false;
        }
        else if (!array_key_exists(0, $this->val) || !array_key_exists(1, $this->val)) {
            throw new \Exception('Invalid between values given. (1)');
        }
        else if (!is_scalar($this->val[0]) || !is_scalar($this->val[1])) {
            throw new \Exception('Invalid between values given. (2)');
        }

        //------------------------------------------------------------------------------
        // Add Bind Parameters
        //------------------------------------------------------------------------------
        $place_holder1 = $this->AddBindParam($this->val[0], $this->type);
        $place_holder2 = $this->AddBindParam($this->val[1], $this->type);

        //------------------------------------------------------------------------------
        // Create and Return Condition
        //------------------------------------------------------------------------------
        return "{$this->field} {$this->op} {$place_holder1} AND {$place_holder2}";
    }

}
