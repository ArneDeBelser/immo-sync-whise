<?php

namespace ADB\ImmoSyncWhise\Matchers;

use ADB\ImmoSyncWhise\Lookup\Category;
use ADB\ImmoSyncWhise\Lookup\Country;
use ADB\ImmoSyncWhise\Lookup\DisplayStatus;
use ADB\ImmoSyncWhise\Lookup\Purpose;
use ADB\ImmoSyncWhise\Lookup\PurposeStatus;
use ADB\ImmoSyncWhise\Lookup\Status;
use ADB\ImmoSyncWhise\Lookup\SubCategory;
use ADB\ImmoSyncWhise\Matchers\Contracts\MatcherContract;
use DateTime;

class EstateMatcher implements MatcherContract
{
    public $lookups = [
        '_iws_displayStatusId' => DisplayStatus::class,
        '_iws_category' => Category::class,
        '_iws_subCategory' => SubCategory::class,
        '_iws_country' => Country::class,
        '_iws_status' => Status::class,
        '_iws_purpose' => Purpose::class,
        '_iws_purposeStatus' => PurposeStatus::class,
    ];

    public array $titles = [];

    public array $descriptions = [];

    public function __construct()
    {
        $this->titles = [
            '_iws_id' => __('Estate ID', 'immo-sync-whise'),
            '_iws_address' => __('Estate Address', 'immo-sync-whise'),
            '_iws_canHaveChildren' => __('Can have children', 'immo-sync-whise'),
            '_iws_area' => __('Area', 'immo-sync-whise'),
            '_iws_bathRooms' => __('Bathrooms', 'immo-sync-whise'),
            '_iws_box' => __('Box', 'immo-sync-whise'),
            '_iws_city' => __('City', 'immo-sync-whise'),
            '_iws_client' => __('Client', 'immo-sync-whise'),
            '_iws_clientId' => __('Client ID', 'immo-sync-whise'),
            '_iws_createDateTime' => __('Created', 'immo-sync-whise'),
            '_iws_currency' => __('Currency', 'immo-sync-whise'),
            '_iws_displayAddress' => __('Display Address', 'immo-sync-whise'),
            '_iws_displayPrice' => __('Display Price', 'immo-sync-whise'),
            '_iws_displayStatusId' => __('Display Status ID', 'immo-sync-whise'),
            '_iws_energyClass' => __('Energy class', 'immo-sync-whise'),
            '_iws_energyValue' => __('Energy value', 'immo-sync-whise'),
            '_iws_estateOrder' => __('Estate order', 'immo-sync-whise'),
            '_iws_floor' => __('Floor', 'immo-sync-whise'),
            '_iws_fronts' => __('Fronts', 'immo-sync-whise'),
            '_iws_furnished' => __('Furnished', 'immo-sync-whise'),
            '_iws_garage' => __('Garage', 'immo-sync-whise'),
            '_iws_garden' => __('Garden', 'immo-sync-whise'),
            '_iws_gardenArea' => __('Garden area', 'immo-sync-whise'),
            '_iws_groundArea' => __('Ground area', 'immo-sync-whise'),
            '_iws_longDescription' => __('Long description', 'immo-sync-whise'),
            '_iws_maxArea' => __('Max area', 'immo-sync-whise'),
            '_iws_minArea' => __('Min area', 'immo-sync-whise'),
            '_iws_name' => __('Estate name', 'immo-sync-whise'),
            '_iws_number' => __('Estate number', 'immo-sync-whise'),
            '_iws_office' => __('Office', 'immo-sync-whise'),
            '_iws_officeId' => __('Office ID', 'immo-sync-whise'),
            '_iws_parentId' => __('Parent ID', 'immo-sync-whise'),
            '_iws_parking' => __('Parking', 'immo-sync-whise'),
            '_iws_price' => __('Price', 'immo-sync-whise'),
            '_iws_publicationText' => __('Publication text', 'immo-sync-whise'),
            '_iws_priceChangeDateTime' => __('Price last changed', 'immo-sync-whise'),
            '_iws_referenceNumber' => __('Reference number', 'immo-sync-whise'),
            '_iws_rooms' => __('Rooms', 'immo-sync-whise'),
            '_iws_shortDescription' => __('Short description', 'immo-sync-whise'),
            '_iws_sms' => __('SMS', 'immo-sync-whise'),
            '_iws_terrace' => __('Terrace', 'immo-sync-whise'),
            '_iws_updateDateTime' => __('Last updated', 'immo-sync-whise'),
            '_iws_zip' => __('ZIP', 'immo-sync-whise'),
            '_iws_category' => __('Category', 'immo-sync-whise'),
            '_iws_subCategory' => __('Sub category', 'immo-sync-whise'),
            '_iws_country' => __('Country', 'immo-sync-whise'),
            '_iws_status' => __('Status', 'immo-sync-whise'),
            '_iws_availability' => __('Availability', 'immo-sync-whise'),
            '_iws_purpose' => __('Purpose', 'immo-sync-whise'),
            '_iws_purposeStatus' => __('Porpuse status', 'immo-sync-whise'),
            '_iws_state' => __('State', 'immo-sync-whise'),
            '_iws_pictures' => __('Pictures', 'immo-sync-whise'),
            '_iws_details' => __('Details', 'immo-sync-whise'),
        ];

        $this->descriptions = [
            '_iws_id' => __('The whise ID of the estate', 'immo-sync-whise'),
            '_iws_address' => __('The address of the estate', 'immo-sync-whise'),
            '_iws_canHaveChildren' => __('Does estate have children', 'immo-sync-whise'),
            '_iws_area' => __('The area of the estate', 'immo-sync-whise'),
            '_iws_bathRooms' => __('Amount of bathrooms', 'immo-sync-whise'),
            '_iws_box' => __('Box', 'immo-sync-whise'),
            '_iws_city' => __('Estate city', 'immo-sync-whise'),
            '_iws_client' => __('Associated Whise Client', 'immo-sync-whise'),
            '_iws_clientId' => __('Associated Whise Client ID', 'immo-sync-whise'),
            '_iws_createDateTime' => __('Creation time', 'immo-sync-whise'),
            '_iws_currency' => __('Currency', 'immo-sync-whise'),
            '_iws_displayAddress' => __('Should estate address be displayed', 'immo-sync-whise'),
            '_iws_displayPrice' => __('Should estate price be displayed', 'immo-sync-whise'),
            '_iws_displayStatusId' => __('Display Status ID', 'immo-sync-whise'),
            '_iws_energyClass' => __('The energy class of the estate', 'immo-sync-whise'),
            '_iws_energyValue' => __('The energy value of the estate', 'immo-sync-whise'),
            '_iws_estateOrder' => __('The hierarchical order of the estates', 'immo-sync-whise'),
            '_iws_floor' => __('Amount of floors', 'immo-sync-whise'),
            '_iws_fronts' => __('Amount of fronts', 'immo-sync-whise'),
            '_iws_furnished' => __('Is the estate furnished or not', 'immo-sync-whise'),
            '_iws_garage' => __('Amount of garages', 'immo-sync-whise'),
            '_iws_garden' => __('Amount of gardens', 'immo-sync-whise'),
            '_iws_gardenArea' => __('Garden area of the estate', 'immo-sync-whise'),
            '_iws_groundArea' => __('Ground area of the estate', 'immo-sync-whise'),
            '_iws_longDescription' => __('Estate Long description', 'immo-sync-whise'),
            '_iws_maxArea' => __('Max area of the estate', 'immo-sync-whise'),
            '_iws_minArea' => __('Min area of the estate', 'immo-sync-whise'),
            '_iws_name' => __('Name of the estate', 'immo-sync-whise'),
            '_iws_number' => __('Number of the estate', 'immo-sync-whise'),
            '_iws_office' => __('Associated Whise Office', 'immo-sync-whise'),
            '_iws_officeId' => __('Associated Whise Office ID', 'immo-sync-whise'),
            '_iws_parentId' => __('Associated Whise Parent ID', 'immo-sync-whise'),
            '_iws_parking' => __('Amount of parking sports', 'immo-sync-whise'),
            '_iws_price' => __('The price of the estate', 'immo-sync-whise'),
            '_iws_publicationText' => __('Publication text', 'immo-sync-whise'),
            '_iws_priceChangeDateTime' => __('When was the price last changed', 'immo-sync-whise'),
            '_iws_referenceNumber' => __('Whise Reference number', 'immo-sync-whise'),
            '_iws_rooms' => __('Amount of rooms', 'immo-sync-whise'),
            '_iws_shortDescription' => __('Estate Short description', 'immo-sync-whise'),
            '_iws_sms' => __('SMS', 'immo-sync-whise'),
            '_iws_terrace' => __('Amount of terraces', 'immo-sync-whise'),
            '_iws_updateDateTime' => __('Estate Last updated', 'immo-sync-whise'),
            '_iws_zip' => __('Estate ZIP', 'immo-sync-whise'),
            '_iws_category' => __('Whise Category', 'immo-sync-whise'),
            '_iws_subCategory' => __('Whise Sub category', 'immo-sync-whise'),
            '_iws_country' => __('Whise Country', 'immo-sync-whise'),
            '_iws_status' => __('Whise Status', 'immo-sync-whise'),
            '_iws_availability' => __('Whise Availability', 'immo-sync-whise'),
            '_iws_purpose' => __('Whise Purpose', 'immo-sync-whise'),
            '_iws_purposeStatus' => __('Whise Porpuse status', 'immo-sync-whise'),
            '_iws_state' => __('Whise State', 'immo-sync-whise'),
            '_iws_pictures' => __('Whise Pictures', 'immo-sync-whise'),
            '_iws_details' => __('Whise Details', 'immo-sync-whise'),
        ];
    }

    public function getTitle(string $key)
    {
        return array_key_exists($key, $this->titles) ? $this->titles[$key] : 'Undefined';
    }

    public function getDescription(string $key)
    {
        return array_key_exists($key, $this->descriptions) ? $this->descriptions[$key] : '';
    }

    public function getField($key, $field)
    {
        if (array_key_exists($key, $this->lookups)) {
            return $this->getFieldValueFromLookup($key, $field[0]);
        }

        $field = is_array($field) ? end($field) : $field;

        if (is_serialized($field)) {
            $object = unserialize($field);

            if ($object instanceof DateTime) {
                return $object->date;
            }

            // This is some hocus pocus to get the value from FW4 Whise
            if (gettype($object) == 'array') {
                if (property_exists($object[0], 'content')) {
                    return $object[0]->content;
                } else {
                    return $object[0]->getData()['content'];
                }
            }
        } else {
            return $field;
        }
    }

    public function getFieldValueFromLookup($key, $field)
    {
        $class = new $this->lookups[$key];
        $key = array_search($field, array_column($class->lookup, 'id'));
        $value = $class->lookup[$key];

        return $value['name'];
    }
}
