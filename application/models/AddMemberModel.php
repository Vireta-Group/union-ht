<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Family data-sheet and family-member access.
 *
 * TENANT SCOPING
 * Neither table's reads and deletes were filtered by parishad, so any logged-in
 * parishad admin could reach any other parishad's rows by passing an id:
 *
 *   - delete_member() ran a bare `DELETE FROM family_member WHERE fm_id = ?`,
 *     and fm_id is a sequential bigint — a rupsha admin could delete a কাঞ্চন
 *     family member by guessing.
 *   - get_datasheet_by_id() is called with $this->input->get('id'), so
 *     /dashboard/family_dataset_form?id=1234 read tenant 1's data sheet from
 *     tenant 10's browser.
 *
 * The host-based cross-tenant path is closed separately, in
 * Dashboard::__construct(). That guard decides WHICH parishad you are; it
 * cannot stop you naming a row id belonging to another one. Isolation against
 * a guessed primary key has to live on the query, here.
 *
 * Both tables already carry a tenant column, so this needed no schema change
 * and no join:
 *   family_data_sheet.ins_aut_iiid  — the parishad id, i.e. tenant_code()
 *   family_member.in_s_pr_id_dd     — the same, written by Dashboard's insert
 *                                    ('in_s_pr_id_dd' => tenant_code())
 *
 * The inserts deliberately do not set the tenant column themselves: they take
 * the caller's $data verbatim, and every caller is a controller that already
 * supplies it. Adding a silent default here would hide a caller that forgets.
 *
 * Note for whoever reads the docs next: docs/REDESIGN-SCOPE.md item A8 still
 * says family_member has no tenant column. It did not; the column is there and
 * was already being written. That entry is stale.
 */
class AddMemberModel extends CI_Model {

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function get_family_members_by_main_id($main_id)
    {
        $this->db->select('*');
        $this->db->where('family_datasid', $main_id);
        $this->db->where('in_s_pr_id_dd', tenant_code());
        $sql = $this->db->get('family_member');
        return $sql->result(); 
    }

    public function delete_member($id)
    {
        // Scoped on purpose. This used to be a bare `DELETE FROM family_member
        // WHERE fm_id = ?`, and fm_id is a sequential bigint — so a tenant admin
        // who guessed one deleted another parishad's family member and was told
        // it worked. With the filter a cross-tenant id matches zero rows and
        // deletes nothing. See the class docblock.
        $this->db->where('fm_id', $id);
        $this->db->where('in_s_pr_id_dd', tenant_code());
        $this->db->delete('family_member');

        return $this->db->affected_rows();
    }

	public function insert_data_sheet_form_data($data)
	{
        $this->db->insert('family_data_sheet', $data);
        return $this->db->insert_id();
	}

    public function get_uniq_id($data)
    {
        $this->db->where('uniqID', $data);
        $this->db->where('ins_aut_iiid', tenant_code());
		$sql = $this->db->get('family_data_sheet');
		return $sql->row();
    }

    public function get_datasheet_by_id($id)
    {
        // Called with $this->input->get('id') from
        // Dashboard::family_dataset_form(), so an unfiltered read here is a
        // direct object reference: /dashboard/family_dataset_form?id=1234
        // returned tenant 1's holding to whoever asked. See the class docblock.
        $this->db->where('data_id', $id);
        $this->db->where('ins_aut_iiid', tenant_code());
		$sql = $this->db->get('family_data_sheet');
		return $sql->row();
    }

    public function insert_family_member_data_sheet_form_data($data)
	{
        $this->db->insert('family_member', $data);
        return $this->db->insert_id();
	}

}