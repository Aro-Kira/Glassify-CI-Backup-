<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Download_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Get all downloadable items for a customer.
     * This includes quotation PDFs and other downloadable files from orders.
     * 
     * @param int $customer_id
     * @return array Array of download objects
     */
    public function get_customer_downloads($customer_id)
    {
        $this->db->select('
            o.OrderID,
            o.OrderNumber,
            o.QuotationPDFUrl as DownloadLink,
            o.OrderDate,
            DATE_ADD(o.OrderDate, INTERVAL 30 DAY) as ExpiryDate,
            p.ProductName
        ');
        $this->db->from('`order` o');
        $this->db->join('order_items oi', 'oi.OrderID = o.OrderID', 'left');
        $this->db->join('product p', 'p.Product_ID = oi.Product_ID', 'left');
        $this->db->where('o.Customer_ID', $customer_id);
        $this->db->where('o.QuotationPDFUrl IS NOT NULL');
        $this->db->where('o.QuotationPDFUrl !=', '');
        $this->db->group_by('o.OrderID');
        $this->db->order_by('o.OrderDate', 'DESC');
        
        return $this->db->get()->result();
    }
}
