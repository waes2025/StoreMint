<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'type',
        'supplier_business_name',
        'name',
        'prefix',
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'contact_id',
        'contact_status',
        'tax_number',
        'city',
        'state',
        'country',
        'address_line_1',
        'address_line_2',
        'zip_code',
        'mobile',
        'landline',
        'alternate_number',
        'pay_term_number',
        'pay_term_type',
        'credit_limit',
        'created_by',
        'converted_by',
        'converted_on',
        'balance',
        'total_rp',
        'total_rp_used',
        'total_rp_expired',
        'is_default',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_country',
        'shipping_zip_code',
        'customer_group_id',
        'crm_source',
        'crm_life_stage',
        'custom_field1',
        'custom_field2',
        'custom_field3',
        'custom_field4',
        'custom_field5',
        'custom_field6',
        'custom_field7',
        'custom_field8',
        'custom_field9',
        'custom_field10',
        'shipping_custom_field_details',
    ];
}
