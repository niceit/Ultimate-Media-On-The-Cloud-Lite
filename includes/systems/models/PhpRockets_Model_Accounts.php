<?php if (!defined('ULTIMATE_MEDIA_PLG_LOADED')) { die('Zero Handle'); }
/**
 * Simple Model for Storage Accounts
 * Package: Ultimate Media On The Cloud
 * Plugin URI: https://wordpress.org/extend/plugins/ultimate-media-on-the-cloud-lite
 * Date: 19-Jun-2019
 */
if (!class_exists('PhpRockets_Model_Accounts')) {
    class PhpRockets_Model_Accounts extends PhpRockets_Models
    {
        private static $table = 'accounts';
        private static $alias = 'ucma';

        /**
         * @param        $args
         * Arguments can contains
         *  fields [] list of fields will be selected
         *  condition [] list of condition will be queried to table
         *  order_by string field and order by keyword (ASC/DESC)
         *  limit int limit the result
         * @param string $output
         *
         * @return mixed
         */
        public static function query($args, $output = ARRAY_A)
        {
            global $wpdb;
            $sql = 'SELECT ';

            $fields = isset($args['fields']) ? implode(', '. self::$alias .'.', $args['fields']) : self::$alias .'.*';
            $sql .= $fields;
            $sql .= ' FROM `'. $wpdb->prefix . self::$configs->plugin_db_prefix . self::$table .'` '. self::$alias .' ';

            /**
             * Apply the conditions
            **/
            if (isset($args['conditions'])) {
                $sql .= ' WHERE ';
                $cond_count = 1;
                foreach ($args['conditions'] as $key => $condition) {
                    if ($cond_count > 1) {
                        $sql .= ' AND ';
                    }

                    if (!is_array($condition)) {
                        $sql .= ($key .'='. (is_numeric($condition) ? $condition : "'{$condition}'"));
                    } else {
                        $sql .= ($key . $condition['operator'] . (is_numeric($condition['value']) ? $condition['value'] : "'{$condition['value']}'"));
                    }
                    ++$cond_count;
                }

                if (isset($args['conditions']['id']) && !is_array($args['conditions']['id'])) {
                    $args['limit'] = 1;
                }
            }

            if (isset($args['order_by'])) {
                $sql .= ' ORDER BY '. $args['order_by'];
            }

            if (isset($args['limit'])) {
                $sql .= ' LIMIT '. $args['limit'];
            }

            if (isset($args['limit']) && (int)$args['limit'] === 1) {
                $data = $wpdb->get_row($sql, $output);
            } else {
                $data = $wpdb->get_results($sql, $output);
            }

            return $data;
        }

        public static function create($data)
        {
            global $wpdb;
            return $wpdb->insert($wpdb->prefix . self::$configs->plugin_db_prefix . self::$table, $data);
        }

        public static function update($id, $data)
        {
            global $wpdb;
            return $wpdb->update($wpdb->prefix . self::$configs->plugin_db_prefix . self::$table, $data, [
                'id' => $id
            ]);
        }

        public static function setAllNoneDefault($storage_adapter, $except_id = null)
        {
            global $wpdb;
            $sql = 'UPDATE '. $wpdb->prefix . self::$configs->plugin_db_prefix . self::$table ." SET is_default = 0 WHERE storage_adapter = '{$storage_adapter}'";
            if ($except_id) {
                $sql .= ' AND id <> '. $except_id;
            }

            $wpdb->query($sql);
        }

        public static function delete($id)
        {
            global $wpdb;
            return $wpdb->delete($wpdb->prefix . self::$configs->plugin_db_prefix . self::$table, ['id' => $id]);
        }
    }
}