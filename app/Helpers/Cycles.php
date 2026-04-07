<?php
namespace App\Helpers;

// Import Model Class here

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Cycles
{
	protected $nonRecurringCycles = ["onetime" => "One Time", "freeaccount" => "Free Account", "free" => "Free Account"];
	protected $recurringCycles = array("monthly" => "Monthly", "quarterly" => "Quarterly", "semiannually" => "Semi-Annually", "annually" => "Annually", "biennially" => "Biennially", "triennially" => "Triennially");
	protected $monthsToCyclesMap = [
        1 => "Monthly", 
        3 => "Quarterly", 
        6 => "Semi-Annually", 
        12 => "Annually", 
        24 => "Biennially", 
        36 => "Triennially"
    ];
	const CYCLE_FREE = "free";
	const CYCLE_ONETIME = "onetime";
	const DISPLAY_FREE = "Free Account";
	const DISPLAY_ONETIME = "One Time";

	public function getSystemBillingCycles($excludeNonRecurring = false)
	{
		if ($excludeNonRecurring) {
			$allCycles = $this->getRecurringCycles();
		} else {
			$allCycles = array_merge($this->nonRecurringCycles, $this->getRecurringCycles());
		}
		$cycles = array();
		foreach ($allCycles as $k => $v) {
			$cycles[] = $k;
		}
		return $cycles;
	}

	public function getRecurringSystemBillingCycles()
    {
        return $this->getSystemBillingCycles(true);
    }
    public function setMonthsToCyclesMap()
    {
        $this->monthsToCyclesMap = [
            1 => "Monthly", 
            3 => "Quarterly", 
            6 => "Semi-Annually", 
            12 => "Annually", 
            24 => "Biennially", 
            36 => "Triennially"
        ];
    }
	public function getNumberOfMonths($cycle)
	{
        $cycles = array_flip($this->monthsToCyclesMap);
		if (array_key_exists($cycle, $cycles)) {
            return $cycles[$cycle];
		}
		$normalisedCycle = $this->getNormalisedBillingCycle($cycle);
		$cycle = $this->getPublicBillingCycle($normalisedCycle);
		if (array_key_exists($cycle, $cycles)) {
            return $cycles[$cycle];
		}
		throw new \Exception("Invalid billing cycle provided");
	}

	public function getNormalisedBillingCycle($cycle)
	{
		$cycle = strtolower($cycle);
		$cycle = preg_replace("/[^a-z]/i", "", $cycle);
		if ($cycle == "freeaccount") {
			$cycle = "free";
		}
		return $this->isValidSystemBillingCycle($cycle) ? $cycle : "";
	}

	public function isValidSystemBillingCycle($cycle)
	{
		return in_array($cycle, $this->getSystemBillingCycles());
	}
	
	public function isValidPublicBillingCycle($cycle)
    {
        return in_array($cycle, $this->getPublicBillingCycles());
    }

	public function getPublicBillingCycles()
    {
        $allCycles = array_merge($this->nonRecurringCycles, $this->getRecurringCycles());
        $cycles = array();
        foreach ($allCycles as $k => $v) {
            $cycles[] = $v;
        }
        return $cycles;
    }

	public function getPublicBillingCycle($cycle)
	{
		$allCycles = array_merge($this->nonRecurringCycles, $this->getRecurringCycles());
		return array_key_exists($cycle, $allCycles) ? $allCycles[$cycle] : "";
	}

	public function getRecurringCycles()
	{
		return $this->recurringCycles;
	}

	public static function cyclesDropDown($billingcycle = "", $any = "", $freeop = "", $name = "billingcycle", $onchange = "", $id = "", $initWithSelectTag = false)
    {
        if (!$freeop) {
            $freeop = __("admin.billingcyclesfree");
        }

        if ($onchange) {
            $onchange = "onchange=\"" . $onchange . "\"";
        }

        if ($id) {
            $id = "id=\"" . $id . "\"";
        }

		$code = "";
		if ($initWithSelectTag) {
			$code .= "<select name=\"" . $name . "\" class=\"form-control select-inline\"" . $onchange . $id . ">";
		}
        
		if ($any) {
            $code .= "<option value=\"\">" . __("admin.any") . "</option>";
        }

        $code .= "<option value=\"Free Account\"";
        if (strcasecmp($billingcycle, "Free Account") === 0) {
            $code .= " selected";
        }

        $code .= ">" . $freeop . "</option>";
        $code .= "<option value=\"One Time\"";
        if (in_array(strtolower($billingcycle), array("one time", "onetime"))) {
            $code .= " selected";
        }

        $code .= ">" . __("admin.billingcyclesonetime") . "</option>";
        $code .= "<option value=\"Monthly\"";
        if (strcasecmp($billingcycle, "Monthly") === 0) {
            $code .= " selected";
        }

        $code .= ">" . __("admin.billingcyclesmonthly") . "</option>";
        $code .= "<option value=\"Quarterly\"";
        if (strcasecmp($billingcycle, "Quarterly") === 0) {
            $code .= " selected";
        }

        $code .= ">" . __("admin.billingcyclesquarterly") . "</option>";
        $code .= "<option value=\"SemiAnnually\"";
        if (strcasecmp($billingcycle, "Semi-Annually") === 0) {
            $code .= " selected";
        }

        $code .= ">" . __("admin.billingcyclessemiannually") . "</option>";
        $code .= "<option value=\"Annually\"";
        if (strcasecmp($billingcycle, "Annually") === 0) {
            $code .= " selected";
        }

        $code .= ">" . __("admin.billingcyclesannually") . "</option>";
        $code .= "<option value=\"Biennially\"";
        if (strcasecmp($billingcycle, "Biennially") === 0) {
            $code .= " selected";
        }

        $code .= ">" . __("admin.billingcyclesbiennially") . "</option>";
        $code .= "<option value=\"Triennially\"";
        if (strcasecmp($billingcycle, "Triennially") === 0) {
            $code .= " selected";
        }

        $code .= ">" . __("admin.billingcyclestriennially") . "</option>";

		if ($initWithSelectTag) {
        	$code .= "</select>";
		}

        return $code;
    }
    
	public function getNameByMonths($months)
    {
        return isset($this->monthsToCyclesMap[$months]) ? $this->monthsToCyclesMap[$months] : "";
    }

	public function isRecurring($cycle)
    {
        $recurringCycles = $this->getRecurringCycles();
        if (in_array($cycle, $recurringCycles) || array_key_exists($cycle, $recurringCycles)) {
            return true;
        }
        return false;
    }

	public function translate($cycle)
    {
        return \Lang::get("orderpaymentterm" . $this->getNormalisedBillingCycle($cycle));
    }

	public function getGreaterCycles($cycle)
    {
        $currentCycleMonths = $this->getNumberOfMonths($cycle);
        $cyclesToReturn = array();
        foreach ($this->monthsToCyclesMap as $numMonths => $displayLabel) {
            if ($currentCycleMonths <= $numMonths && $numMonths != 100) {
                $cyclesToReturn[] = $this->getNormalisedBillingCycle($displayLabel);
            }
        }
        return $cyclesToReturn;
    }

    public static function isFree($cycle)
    {
        $cycle = strtolower($cycle);
        return in_array($cycle, array(strtolower(self::CYCLE_FREE), strtolower(self::DISPLAY_FREE)));
    }

}
