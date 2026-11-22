<?php
// Each entry describes one admin table. Filters and report exports use the same columns.
function catalog(string $type): array
{
    if ($type === 'farmers' || $type === 'buyers') {
        $farmer   = $type === 'farmers';
        $role     = $farmer ? 'farmer' : 'buyer';
        $profile  = $farmer ? 'farmer_profiles' : 'buyer_profiles';
        $activity = $farmer
            ? "(SELECT COUNT(*) FROM crop_listings l WHERE l.farmer_id=u.user_id AND l.listing_status <> 'removed')"
            : '(SELECT COUNT(*) FROM orders o WHERE o.buyer_id=u.user_id)';
        $business = $farmer ? 'p.farm_name' : 'p.business_name';
        return [
            'sql' => "SELECT u.user_id id,u.full_name name,u.mobile,u.email,p.district location,
                    $business business,$activity activity,u.account_status status,u.created_at recorded
                    FROM users u LEFT JOIN $profile p ON p.{$role}_id=u.user_id WHERE u.role='$role'",
            'search'  => ['id', 'name', 'mobile', 'email', 'business'],
            'columns' => ['id' => 'ID', 'name' => 'Name', 'business' => $farmer ? 'Farm' : 'Business', 'mobile' => 'Mobile', 'location' => 'District', 'activity' => $farmer ? 'Listings' : 'Orders', 'status' => 'Status'],
            'extra'   => ['email' => 'Email', 'recorded' => 'Registered'],
        ];
    }
    if ($type === 'listings') {
        return [
            'sql'     => "SELECT l.listing_id id,l.crop_name crop,u.full_name farmer,c.category_name category,
                l.quantity_available quantity,l.quantity_unit unit,l.price_per_unit price,l.variety,l.description,
                l.listing_status status,l.created_at recorded FROM crop_listings l
                JOIN users u ON u.user_id=l.farmer_id LEFT JOIN crop_categories c ON c.category_id=l.category_id",
            'search'  => ['id', 'crop', 'farmer', 'variety'],
            'columns' => ['id' => 'ID', 'crop' => 'Crop', 'farmer' => 'Farmer', 'category' => 'Category', 'quantity' => 'Quantity', 'unit' => 'Unit', 'price' => '₹ / unit', 'status' => 'Status'],
            'extra'   => ['variety' => 'Variety', 'description' => 'Description', 'recorded' => 'Listed'],
        ];
    }

    if ($type === 'orders' || $type === 'transactions') {
        return [
            'sql'     => "SELECT o.order_id id,o.order_number reference,b.full_name buyer,f.full_name farmer,l.crop_name crop,
                o.quantity,o.quantity_unit unit,o.unit_price price,o.subtotal amount,o.delivery_address address,
                o.buyer_note note,o.order_status status,o.placed_at recorded FROM orders o
                JOIN users b ON b.user_id=o.buyer_id JOIN users f ON f.user_id=o.farmer_id
                JOIN crop_listings l ON l.listing_id=o.listing_id",
            'search'  => ['id', 'reference', 'buyer', 'farmer', 'crop'],
            'columns' => ['reference' => 'Order', 'buyer' => 'Buyer', 'farmer' => 'Farmer', 'crop' => 'Crop', 'quantity' => 'Quantity', 'unit' => 'Unit', 'amount' => 'Order value (₹)', 'recorded' => 'Placed', 'status' => 'Status'],
            'extra'   => ['price' => '₹ / unit', 'address' => 'Delivery address', 'note' => 'Buyer note'],
        ];
    }

    return [
        'sql'     => "SELECT CAST(c.complaint_id AS SIGNED) id,c.complaint_number,c.priority,u.full_name name,u.role,c.order_id,c.subject,c.description,
                c.complaint_status AS status,c.created_at recorded,u.email,u.mobile,NULL reference_text,NULL reply,NULL file_type,0 revision
                FROM complaints c LEFT JOIN users u ON u.user_id=c.raised_by
                UNION ALL
                SELECT -CAST(request_id AS SIGNED),ticket_number,NULL,contact_name,contact_role,NULL,subject,description,
                status,created_at,email,mobile,reference_text,reply,file_type,revision FROM support_requests",
        'search'  => ['id', 'complaint_number', 'subject', 'name', 'order_id'],
        'columns' => ['complaint_number' => 'Complaint number', 'priority' => 'Priority', 'name' => 'Raised by', 'role' => 'Role', 'subject' => 'Subject', 'recorded' => 'Opened', 'status' => 'Status'],
        'extra'   => ['order_id' => 'Order ID', 'reference_text' => 'Submitted reference', 'email' => 'Contact email', 'mobile' => 'Phone', 'description' => 'Description', 'reply' => 'Support response'],
    ];
}
