<?php
namespace App\Helpers;

// Import Model Class here
use App\Models\Upgrade;

// Import Package Class here

// Import Laravel Class here
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class UpgradeCalculator
{
	protected $upgradeEntity = NULL;
    protected $upgradeTarget = NULL;
    protected $upgradeBillingCycle = NULL;
    protected $upgradeOutput = NULL;
	public function setUpgradeTargets($upgradeEntity, $upgradeTarget, $upgradeBillingCycle = NULL)
    {
        if ($upgradeEntity instanceof \App\Models\Hosting) {
            $requiredUpgradeObject = "\\App\\Models\\Product";
        } else {
            if ($upgradeEntity instanceof \App\Models\Hostingaddon) {
                $requiredUpgradeObject = "\\App\\Models\\Addon";
            } else {
                throw new \InvalidArgumentException("Invalid original model");
            }
        }
        if (!$upgradeTarget instanceof $requiredUpgradeObject) {
            throw new \InvalidArgumentException("Upgrade model must be of type: " . $requiredUpgradeObject);
        }
        $this->upgradeEntity = $upgradeEntity;
        $this->upgradeTarget = $upgradeTarget;
        $this->upgradeBillingCycle = $upgradeBillingCycle;
        return $this;
    }
	// next
	public function calculate()
    {
        $billingCycle = $this->upgradeBillingCycle;
        if (!$billingCycle) {
            $billingCycle = $this->upgradeEntity->billingcycle;
        }
        $userId = $this->upgradeEntity->userid;
        $currency = (new \App\Helpers\AdminFunctions())->getCurrency($userId);
        $pricing = $this->upgradeTarget->pricing($currency)->byCycle($billingCycle);
        if (is_null($pricing)) {
            throw new \Exception("Invalid billing cycle for upgrade");
        }
        $newSetupFee = $pricing->setup()->toNumeric();
        $newRecurringAmount = $pricing->price()->toNumeric();
        $creditCalc = $this->calculateCredit();
        $amountDueToday = $newRecurringAmount - $creditCalc["creditAmount"];
        if ($amountDueToday < 0) {
            $amountDueToday = 0;
        }
        $upgrade = new Upgrade;
        $upgrade->userId = $userId;
        $upgrade->date = \Carbon\Carbon::now();
        $upgrade->type = $this->getUpgradeType();
        $upgrade->entityId = $this->upgradeEntity->id;
        $upgrade->originalValue = $this->getUpgradeEntityProductIdValue();
        $upgrade->newValue = $this->upgradeTarget->id;
        $upgrade->newCycle = $billingCycle;
        $upgrade->localisedNewCycle = (new \App\Helpers\Cycles())->translate($billingCycle);
        $upgrade->upgradeAmount = new \App\Helpers\FormatterPrice($amountDueToday, $currency);
        $upgrade->recurringChange = $newRecurringAmount - $this->upgradeEntity->recurringFee;
        $upgrade->newRecurringAmount = new \App\Helpers\FormatterPrice($newRecurringAmount, $currency);
        $upgrade->creditAmount = new \App\Helpers\FormatterPrice($creditCalc["creditAmount"], $currency);
        $upgrade->daysRemaining = $creditCalc["daysRemaining"];
        $upgrade->totalDaysInCycle = $creditCalc["totalDaysInCycle"];
        $upgrade->applyTax = $this->upgradeTarget->applyTax;
        return $upgrade;
    }
    protected function isServiceUpgrade()
    {
        return $this->upgradeEntity instanceof \App\Models\Hosting;
    }
    protected function getUpgradeType()
    {
        return $this->isServiceUpgrade() ? Upgrade::TYPE_SERVICE : Upgrade::TYPE_ADDON;
    }
    protected function getUpgradeEntityProductIdValue()
    {
        return $this->isServiceUpgrade() ? $this->upgradeEntity->packageId : $this->upgradeEntity->addonId;
    }
    protected function calculateCredit()
    {
        $nextDueDate = $this->upgradeEntity->nextduedate;
        $recurringAmount = $this->upgradeEntity->recurringFee;
        $billingCycle = $this->upgradeEntity->billingcycle;
        $daysInCurrentCycle = $this->calculateDaysInCurrentBillingCycle($nextDueDate, $billingCycle);
        if (0 < $daysInCurrentCycle) {
            $dailyRate = $recurringAmount / $daysInCurrentCycle;
        } else {
            $dailyRate = 0;
        }
        $daysRemaining = 0 < $daysInCurrentCycle ? \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($nextDueDate)) : 0;
        $creditAmount = \App\Helpers\Functions::format_as_currency($dailyRate * $daysRemaining);
        return array("totalDaysInCycle" => $daysInCurrentCycle, "daysRemaining" => $daysRemaining, "creditAmount" => $creditAmount);
    }
    public function calculateDaysInCurrentBillingCycle($nextDueDate, $billingCycle)
    {
        if (!(new \App\Helpers\Cycles())->isRecurring($billingCycle)) {
            return 0;
        }
        if (empty($nextDueDate) || $nextDueDate == "0000-00-00") {
            throw new \Exception("Upgrades require products have a valid next due date. Unable to continue.");
        }
        $months = (new \App\Helpers\Cycles())->getNumberOfMonths($billingCycle);
        $nextDueDate = \Carbon\Carbon::parse($nextDueDate);
        $originalDate = clone $nextDueDate;
        return $nextDueDate->subMonths($months)->diffInDays($originalDate);
    }
}
