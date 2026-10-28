<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sadmin_reviews — Central Review & Testimonial Manager
 * =====================================================
 * Controls stakeholder feedback, UP Chairman/Secretary endorsements,
 * citizen ratings, and visibility across the public SaaS landing pages.
 */
class Sadmin_reviews extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->database();
		$this->load->helper(array('url', 'sadmin'));
		require_sadmin();
	}

	/**
	 * List all reviews with category & status aggregates
	 */
	public function api_list()
	{
		$reviews = $this->db->order_by('created_at', 'DESC')->get('platform_reviews')->result_array();

		$counts = array(
			'total'    => count($reviews),
			'pending'  => 0,
			'approved' => 0,
			'archived' => 0,
			'landing'  => 0,
		);

		foreach ($reviews as &$r) {
			$st = $r['status'];
			if (isset($counts[$st])) {
				$counts[$st]++;
			}
			if (!empty($r['show_on_landing'])) {
				$counts['landing']++;
			}
			$r['id'] = (int) $r['id'];
			$r['rating'] = (int) $r['rating'];
			$r['show_on_landing'] = (bool) $r['show_on_landing'];
			$r['featured'] = (bool) $r['featured'];
		}

		sadmin_json(array(
			'success' => TRUE,
			'counts'  => $counts,
			'data'    => $reviews,
		));
	}

	/**
	 * Create a new verified testimonial directly from Super Admin
	 */
	public function api_create()
	{
		$input = sadmin_api_input();
		$author      = trim($input['author'] ?? '');
		$role        = trim($input['role'] ?? 'চেয়ারম্যান');
		$tenant_name = trim($input['tenant_name'] ?? '');
		$service     = trim($input['service'] ?? 'সনদ ও কর সেবা');
		$rating      = (int) ($input['rating'] ?? 5);
		$content     = trim($input['content'] ?? '');
		$content_en  = trim($input['content_en'] ?? '');
		$impact      = trim($input['impact'] ?? '');
		$featured    = !empty($input['featured']);
		$landing     = isset($input['show_on_landing']) ? (bool)$input['show_on_landing'] : TRUE;

		if (empty($author) || empty($content)) {
			sadmin_json(array('success' => FALSE, 'message' => 'নাম ও মন্তব্য আবশ্যক।'));
			return;
		}

		$data = array(
			'author'          => $author,
			'role'            => $role,
			'tenant_name'     => $tenant_name ?: 'সেন্ট্রাল প্ল্যাটফর্ম',
			'service'         => $service,
			'rating'          => max(1, min(5, $rating)),
			'impact'          => $impact ?: 'নাগরিক সেবা',
			'content'         => $content,
			'content_en'      => $content_en ?: $content,
			'avatar'          => 'inc/assets/himg/avatar' . rand(1, 3) . '.png',
			'status'          => 'approved',
			'show_on_landing' => $landing,
			'featured'        => $featured,
			'created_at'      => date('Y-m-d H:i:s'),
			'updated_at'      => date('Y-m-d H:i:s'),
		);

		$this->db->insert('platform_reviews', $data);
		$id = $this->db->insert_id();

		sadmin_json(array(
			'success' => TRUE,
			'message' => 'টেস্টিমোনিয়াল সফলভাবে যুক্ত ও অনুমোদিত হয়েছে।',
			'id'      => $id,
			'review'  => array_merge($data, array('id' => $id)),
		));
	}

	/**
	 * Moderate review status (approved, pending, archived)
	 */
	public function api_update_status()
	{
		$input  = sadmin_api_input();
		$id     = (int) ($input['id'] ?? 0);
		$status = trim($input['status'] ?? '');

		if (!in_array($status, array('approved', 'pending', 'archived'))) {
			sadmin_json(array('success' => FALSE, 'message' => 'অবৈধ স্ট্যাটাস।'));
			return;
		}

		$this->db->where('id', $id)->update('platform_reviews', array(
			'status'     => $status,
			'updated_at' => date('Y-m-d H:i:s'),
		));

		sadmin_json(array(
			'success' => TRUE,
			'message' => 'স্ট্যাটাস সফলভাবে আপডেট করা হয়েছে।',
		));
	}

	/**
	 * Toggle landing page showcase visibility
	 */
	public function api_toggle_landing()
	{
		$input = sadmin_api_input();
		$id    = (int) ($input['id'] ?? 0);
		$show  = !empty($input['show']);

		$this->db->where('id', $id)->update('platform_reviews', array(
			'show_on_landing' => $show,
			'updated_at'      => date('Y-m-d H:i:s'),
		));

		sadmin_json(array(
			'success' => TRUE,
			'message' => $show ? 'ল্যান্ডিং পেজে প্রদর্শন চালু করা হয়েছে।' : 'ল্যান্ডিং পেজে প্রদর্শন বন্ধ করা হয়েছে।',
		));
	}

	/**
	 * Permanently delete review
	 */
	public function api_delete()
	{
		$input = sadmin_api_input();
		$id    = (int) ($input['id'] ?? 0);
		$this->db->where('id', $id)->delete('platform_reviews');

		sadmin_json(array(
			'success' => TRUE,
			'message' => 'রিভিউ মুছে ফেলা হয়েছে।',
		));
	}
}
