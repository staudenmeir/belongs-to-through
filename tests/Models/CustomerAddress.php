<?php

namespace Tests\Models;

use Znck\Eloquent\Relations\BelongsToThrough;

class CustomerAddress extends Model
{
    /**
     * @return BelongsToThrough<VendorCustomer, VendorCustomerAddress, $this>
     */
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
