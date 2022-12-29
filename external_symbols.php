<?php

/**
 * Types in this file are external to the project. This file is used during development to resolve these external symbols.
 */

class Nav_Menu_Roles_Import
{
    public function import()
    {
    }
}

class WP_Import
{
    public function import()
    {
    }
}

/* Gravity Forms */

class GFAPI
{
    /**
     * @return iterable|object 
     */
    public static function get_forms()
    {
    }

    public static function delete_form()
    {
    }

    public static function add_feed()
    {
    }

    public static function add_form()
    {
    }

    /**
     * @return iterable|object  
     */
    public static function get_entries()
    {
    }
}

function gravity_form()
{
}

function rgpost()
{
}

/**
 * The gf_user_registration() function is used to return an instance of the GF_User_Registration class.
 *
 * @return GF_User_Registration
 */
function gf_user_registration()
{
}

/**
 * User Registration functionality using the add-on framework
 *
 * Contains most of the functionality of the add-on
 *
 * @see GFFeedAddOn
 */
class GF_User_Registration
{
    /**
	 * Retrieve the set password url for the specified user.
	 *
	 *
	 * @param WP_User $user The user object.
	 *
	 * @since 3.4.4.
	 * @since 4.6 Updated to use get_password_reset_key().
	 *
	 * @return string
	 */
    public function get_set_password_url( WP_User $user )
    {
    }
}

/* ACF (Advanced Custom Fields) */

/**
 * Returns an array of field values (name => value) for a specific post.
 *
 * @param mixed $post_id The post ID where the value is saved. Defaults to the current post.
 * @param boolean $format_value Whether to apply formatting logic. Defaults to true.
 * @return (array|false) Array of values or false if no fields are found.
 */
function get_fields($post_id = null, bool $format_value = true)
{
}

/**
 * Returns the value of a specific field.
 * Intuitive and powerful (much like ACF itself ?), this function can be used to load the value of any field from any location. 
 * Please note that each field type returns different forms of data (string, int, array, etc).
 *
 * @param string $selector The field name or field key.
 * @param mixed $post_id The post ID where the value is saved. Defaults to the current post.
 * @param boolean $format_value Whether to apply formatting logic. Defaults to true.
 * @return mixed The field value.
 */
function get_field(string $selector, $post_id = null, bool $format_value = true)
{
}

/**
 * Returns the settings of all fields saved on a specific post.
 * 
 * Each field contains many settings such as a label, name and type. This function can be used to load these settings as an array along with the field’s value.
 *
 * @param [mixed] $post_id The post ID where the value is saved. Defaults to the current post.
 * @param boolean $format_value Whether to apply formatting logic. Defaults to true.
 * @param boolean $load_value Whether to load the field’s value. Defaults to true.
 * @return array This function will return an array looking something like the following. Please note that each field contains unique settings.
 */
function get_field_objects($post_id = false, bool $format_value = true, bool $load_value = true)
{
}

/**
 * Get all ACF field groups.
 *
 * @return array
 */
function acf_get_field_groups()
{
}

/**
 * Get all ACF fields of a group.
 *
 * @param integer $field_group_id
 * @return array
 */
function acf_get_fields(int $field_group_id)
{
};

/**
 * Updates the value of a specific field.
 *
 * @param string $selector The field name or field key.
 * @param mixed $value The new value.
 * @param [mixed] $post_id The post ID where the value is saved. Defaults to the current post.
 * @return void
 */
function update_field(string $selector, $value, $post_id)
{
}

/* The Events Calendar */

function tribe_get_gcal_link()
{
}
function tribe_get_single_ical_link()
{
}

/* WooCommerce */

/**
 * Retrieves product term ids for a taxonomy.
 *
 * @param integer $product_id
 * @param string $taxonomy
 * @return array
 */
function wc_get_product_term_ids(int $product_id, string $taxonomy)
{
}

/**
 * Main function for returning products, uses the WC_Product_Factory class.
 *
 * @param mixed $the_product Post object or post ID of the product.
 * @return (WC_Product|null|false)
 */
function wc_get_product($the_product = false)
{
}

/**
 * Sets a property in the woocommerce_loop global.
 *
 * @param string $prop Prop to set.
 * @param string $value Value to set.
 * @return void
 */
function wc_set_loop_prop(string $prop, string $value)
{
}

/**
 * Output the start of a product loop. By default this is a UL.
 *
 * @param boolean $echo default: 1 – Should echo?.
 * @return string
 */
function woocommerce_product_loop_start(bool $echo = true)
{
}

/**
 * Get other templates (e.g. product attributes) passing attributes and including the file.
 *
 * @param string $template_name Template name.
 * @param array $args Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path Default path. (default: '').
 * @return void
 */
function wc_get_template(string $template_name, array $args, string $template_path = '', string $default_path = '')
{
}

/**
 * Output the end of a product loop. By default this is a UL.
 *
 * @param boolean $echo default: 1 – Should echo?.
 * @return string
 */
function woocommerce_product_loop_end(bool $echo = true)
{
}

/**
 * Resets the woocommerce_loop global.
 *
 * @return void
 */
function wc_reset_loop()
{
}

/**
 * Wrapper used to get terms for a product.
 *
 * @param  int    $product_id Product ID.
 * @param  string $taxonomy   Taxonomy slug.
 * @param  array  $args       Query arguments.
 * @return array
 */
function wc_get_product_terms($product_id, $taxonomy, $args = array())
{
}

/**
 * Abstract Product Class
 *
 * The WooCommerce product class handles individual product data.
 *
 * @version 3.0.0
 * @package WooCommerce\Abstracts
 */
class WC_Product extends WC_Abstract_Legacy_Product
{
    /**
     * Returns product attributes.
     *
     * @param  string $context What the value is for. Valid values are view and edit.
     * @return array
     */
    public function get_attributes($context = 'view')
    {
    }

    /**
     * Get the product's title. For products this is the product name.
     *
     * @return string
     */
    public function get_title()
    {
    }
}

/**
 * Legacy Abstract Product
 *
 * Legacy and deprecated functions are here to keep the WC_Abstract_Product
 * clean.
 * This class will be removed in future versions.
 *
 * @version  3.0.0
 * @package  WooCommerce\Abstracts
 * @category Abstract Class
 * @author   WooThemes
 */
abstract class WC_Abstract_Legacy_Product extends WC_Data
{
}

/**
 * Abstract WC Data Class
 *
 * Implemented by classes using the same CRUD(s) pattern.
 *
 * @version  2.6.0
 * @package  WooCommerce\Abstracts
 */
abstract class WC_Data
{
}

/**
 * wc_get_products and WC_Product_Query provide a standard way of retrieving products that is safe to use and will not break due to database changes in future WooCommerce versions.
 *
 * @param array $args
 * @return array
 */
function wc_get_products(array $args)
{
}

/**
 * Is_product - Returns true when viewing a single product.
 * https://woocommerce.github.io/code-reference/namespaces/default.html#function_is_product
 *
 * @return boolean
 */
function is_product()
{
}

/* Jetpack */

class Jetpack
{
    /**
     * Check whether or not a Jetpack module is active.
     *
     * @param string $module The slug of a Jetpack module.
     * @return bool
     *
     * @static
     */
    public static function is_module_active($module)
    {
    }

    public static function deactivate_module($module)
    {
    }
}

/* Miscellaneous */

function adrotate_group($group)
{
}
