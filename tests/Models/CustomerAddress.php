<?php

namespace Tests\Models;

use Znck\Eloquent\Relations\BelongsToThrough;

class CustomerAddress extends Model
{
    public function vendorCustomer(): BelongsToThrough
    {
        return $this->belongsToThrough(
            VendorCustomer::class,
            VendorCustomerAddress::class,
            foreignKeyLookup: [VendorCustomerAddress::class => 'id'],
            localKeyLookup: [VendorCustomerAddress::class => 'customer_address_id'],
        );
    }
}
