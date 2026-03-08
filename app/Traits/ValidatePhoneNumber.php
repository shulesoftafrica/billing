<?php

namespace App\Traits;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;

trait ValidatePhoneNumber
{
    public function validatePhone($phoneNumber)
    {
        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            if (!str_starts_with($phoneNumber, '+')) {
                $phoneNumber = '+' . $phoneNumber;
            }

            $number = $phoneUtil->parse($phoneNumber, null);
            $countryCode = $number->getCountryCode();
            $nationalNumber = $number->getNationalNumber();
            $regionCode = $phoneUtil->getRegionCodeForNumber($number);
            $isValid = $phoneUtil->isValidNumber($number);
            $numberType = $phoneUtil->getNumberType($number);

            if ($isValid && ($numberType == PhoneNumberType::MOBILE || $numberType == PhoneNumberType::FIXED_LINE_OR_MOBILE)) {
                return [
                    'country_code' => (string) $countryCode,
                    'full_number' => '+' . $countryCode . $nationalNumber,
                    'region_code' => $regionCode,
                    'national_number' => (string) $nationalNumber,
                ];
            }

            return false;
        } catch (NumberParseException $e) {
            return false;
        }
    }
}
