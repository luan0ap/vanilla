<?php
/**
 * @author Todd Burry <todd@vanillaforums.com>
 * @copyright 2009-2017 Vanilla Forums Inc.
 * @license GPLv2
 */

use Garden\Schema\Schema;

abstract class AbstractApiController extends \Vanilla\Web\Controller {
    /**
     * @var Schema
     */
    private $userFragmentSchema;

    /**
     * Filter unwanted values from an array (particularly empty values from request parameters).
     *
     * @param array $values
     * @return array
     */
    public function filterValues(array $values) {
        $result = array_filter($values, function($val) {
            $valid = true;
            if ($val === '') {
                $valid = false;
            }
            return $valid;
        });
        return $result;
    }

    /**
     * Format a specific field.
     *
     * @param array $row An array representing a database row.
     * @param string $field The field name.
     * @param string $format The source format.
     */
    public function formatField(array &$row, $field, $format) {
        if (array_key_exists($field, $row)) {
            $row[$field] = Gdn_Format::to($row[$field], $format) ?: '<!-- empty -->';
        }
    }

    /**
     * Determine which fields should be expanded, using a request and a field map.
     *
     * @param array $data An array representing request data.
     * @param array $map An array of short-to-full field names (e.g. insertUser => InsertUserID).
     * @param string $field The name of the field where the expand fields can be found.
     * @return array
     */
    protected function getExpandFields(array $data, array $map, $field = 'expand') {
        $result = [];
        if (array_key_exists($field, $data)) {
            $expand = $data[$field];
            foreach ($map as $short => $full) {
                if (in_array($short, $expand)) {
                    $result[] = $full;
                }
            }
        }
        return $result;
    }

    /**
     * Get a simple schema for nesting as an "expand" parameter.
     *
     * @param array $fields Valid values for the expand parameter.
     * @return Schema
     */
    protected function getExpandFragment(array $fields) {
        $result = $this->schema([
            'description' => 'Expand associated records.',
            'items' => [
                'enum' => $fields,
                'type' => 'string'
            ],
            'style' => 'form',
            'type' => 'array'
        ], 'ExpandFragment');

        return $result;
    }

    /**
     * Get the schema for users joined to records.
     *
     * @return Schema Returns a schema.
     */
    public function getUserFragmentSchema() {
        if ($this->userFragmentSchema === null) {
            $this->userFragmentSchema = $this->schema([
                'userID:i' => 'The ID of the user.',
                'name:s' => 'The username of the user.',
                'photoUrl:s' => 'The URL of the user\'s avatar picture.'
            ], 'UserFragment');
        }
        return $this->userFragmentSchema;
    }

    public function options($path) {
        return '';
    }

    /**
     * Verify current user permission, if a particular field is in a data array.
     *
     * @param array $data The data array (e.g. request body fields).
     * @param string $field The protected field name.
     * @param string|array $permission A required permissions.
     * @param int|null $id The ID of the record we are checking the permission of (e.g. category ID).
     */
    public function fieldPermission(array $data, $field, $permission, $id = null) {
        if (array_key_exists($field, $data)) {
            $this->permission($permission, $id);
        }
    }
}
