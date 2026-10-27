<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Sonod (certificate), NID-verification and death-registration access.
 *
 * TENANT SCOPING — read this before adding a query here
 *
 * Filtered on the parishad:
 *   - get_sonod_by_todat(), get_sonod_by_today_by_sonod_title_id() read
 *     sonod_genarates, which carries insididid_pridididid — the column
 *     Sonod::insert paths already write with tenant_code(). Both are reached
 *     only from the Sonod controller, which is itself behind
 *     enforce_tenant_scope().
 *
 * Deliberately NOT filtered, with reasons — do not "fix" these without
 * settling the product question first:
 *
 *   - getSonodByUniqID(), get_dead_person(), get_all_warish_gon_person() look
 *     a certificate up by its uniq id, from the Download controller, and a
 *     citizen holding a sonod issued by one parishad has a legitimate reason to
 *     read it while visiting another. Scoping them to the current tenant would
 *     break that. That is the reason they are open, not an oversight — but note
 *     that Download's constructor has no auth gate at all, so these three are
 *     currently reachable anonymously, and death-registration detail is more
 *     sensitive than a certificate image. Worth a decision.
 *
 *   - getAllGlobalSonod(), get_global_single_sonod_by_sonod_id() and
 *     getGlobalSonodByHead() read `deft_cer_all`, which holds certificate
 *     TYPES and titles, not issued certificates. It has no tenant column and
 *     needs none: it is reference data of the same kind as satting_infos.
 */
class SonodCrudModel extends CI_Model {

    public function __construct() 
    {
        parent::__construct();
        $this->load->database();
    } 

	public function insertGlobalSonod($arry)
    {
        $this->db->insert('deft_cer_all', $arry);
        return $this->db->insert_id();
    }

    public function getAllGlobalSonod()
    {
        return $this->db->get('deft_cer_all')->result();        
    }

    public function get_global_single_sonod_by_sonod_id($id)
    {
        return $this->db
                    ->where('deft_cer_all_au_iddd', (int)$id)
                    ->get('deft_cer_all')
                    ->row();
    }

    public function updateGlobalSonod($id, $data)
    {
        return $this->db
                    ->where('deft_cer_all_au_iddd', (int)$id)
                    ->update('deft_cer_all', $data);
    }

    public function deleteGlobalSonod($id)
    {
        return $this->db
                    ->where('deft_cer_all_au_iddd', (int)$id)
                    ->delete('deft_cer_all');
    }

    public function insert_person_nid_verify_data($arr)
    {
        $this->db->insert('person_verify_nid_data_info_saving', $arr);
        return $this->db->insert_id();
    }

    public function insert_new_sonod_gen($arr)
    {
        $this->db->insert('sonod_genarates', $arr);
        return $this->db->insert_id();
    }  

    public function insert_new_sonod_person_info($ary)
    {
        $this->db->insert('certificate_person_verify_info_editing', $ary);
        return $this->db->insert_id();
    }

    public function get_sonod_by_todat($date)
    {
        return $this->db
                    ->where('dating_stampingsss', $date)
                    ->where('insididid_pridididid', tenant_code())
                    ->order_by('sonod_gen_ididid', 'desc')
                    ->get('sonod_genarates')
                    ->row();
    }

    public function get_sonod_by_today_by_sonod_title_id($date, $title_id)
    {
        return $this->db
                    ->where('dating_stampingsss', $date)
                    ->where('deft_cer_aididid', $title_id)
                    ->where('insididid_pridididid', tenant_code())
                    ->order_by('sonod_gen_ididid', 'desc')
                    ->get('sonod_genarates')
                    ->row();
    }

    public function getGlobalSonodByHead($dat)
    {
        return $this->db
                    ->where('certify_titles', $dat)
                    ->get('deft_cer_all')
                    ->row();
    }

    public function getSonodByUniqID($uniq)
    {
        return $this->db
                    ->select('sonod_genarates.*, COALESCE(sonod_genarates.en_bn_type, deft_cer_all.en_bn_type, 1) as en_bn_type, deft_cer_all.certify_titles, deft_cer_all.certify_detail_ss, certificate_person_verify_info_editing.*, customer_full_info.*, person_verify_nid_data_info_saving.*, cer_gens_personss.*')
                    ->where('sonod_uniqs', $uniq)
                    ->join('deft_cer_all', 'deft_cer_all.deft_cer_all_au_iddd = sonod_genarates.deft_cer_aididid', 'left')
                    ->join('certificate_person_verify_info_editing', 'certificate_person_verify_info_editing.ver_per_info_sonod_uniqs = sonod_genarates.sonod_uniqs', 'left')
                    ->join('customer_full_info', 'customer_full_info.udc_list_auto_p_iidd = sonod_genarates.gen_usr_ididd', 'left')
                    ->join('person_verify_nid_data_info_saving', 'person_verify_nid_data_info_saving.person_v_id = COALESCE(NULLIF(sonod_genarates.sonod_global_verify_uids, 0), NULLIF(certificate_person_verify_info_editing.ver_per_nid_autopr_ididi, 0), sonod_genarates.thisins_cert_defaults_p_idididid)', 'left')
                    ->join('cer_gens_personss', 'cer_gens_personss.cer_gens_personsspr_ididid = sonod_genarates.thisins_cert_defaults_p_idididid', 'left')
                    ->get('sonod_genarates')
                    ->row();
    }

    public function getTenantCustomSonods($tenant_id)
    {
        return $this->db
                    ->where('inst_pr_aidddd_id', (int)$tenant_id)
                    ->order_by('certifys_default_ins_ss_iiidddd', 'desc')
                    ->get('certifys_default_ins_ss')
                    ->result();
    }

    public function getTenantCustomSonodById($id, $tenant_id)
    {
        return $this->db
                    ->where('certifys_default_ins_ss_iiidddd', (int)$id)
                    ->where('inst_pr_aidddd_id', (int)$tenant_id)
                    ->get('certifys_default_ins_ss')
                    ->row();
    }

    public function getTenantCustomSonodByGlobalId($global_id, $tenant_id)
    {
        return $this->db
                    ->where('deft_cer_all_id', (int)$global_id)
                    ->where('inst_pr_aidddd_id', (int)$tenant_id)
                    ->order_by('certifys_default_ins_ss_iiidddd', 'desc')
                    ->get('certifys_default_ins_ss')
                    ->row();
    }

    public function insertTenantCustomSonod($data)
    {
        $this->db->insert('certifys_default_ins_ss', $data);
        return $this->db->insert_id();
    }

    public function updateTenantCustomSonod($id, $tenant_id, $data)
    {
        return $this->db
                    ->where('certifys_default_ins_ss_iiidddd', (int)$id)
                    ->where('inst_pr_aidddd_id', (int)$tenant_id)
                    ->update('certifys_default_ins_ss', $data);
    }

    public function deleteTenantCustomSonod($id, $tenant_id)
    {
        return $this->db
                    ->where('certifys_default_ins_ss_iiidddd', (int)$id)
                    ->where('inst_pr_aidddd_id', (int)$tenant_id)
                    ->delete('certifys_default_ins_ss');
    }

    public function get_global_nid_verify_data_by_idd($id)
    {
        return $this->db
                    ->where('person_v_id', $id)
                    ->get('person_verify_nid_data_info_saving')
                    ->row();
    }

    public function get_global_birth_verifing_data_by_iid($id)
    {
        return $this->db
                    ->where('cer_gens_personsspr_ididid', $id)
                    ->get('cer_gens_personss')
                    ->row();
    }

    public function insert_warish_rel_information_new_per($batch)
    {
        $this->db->insert_batch('warish_somod_person', $batch);
    }

    public function insert_new_warish_person_information($ary)
    {
        $this->db->insert('warish_person_infos', $ary);
        return $this->db->insert_id();
    }

    public function get_all_warish_gon_person($id)
    {
        return $this->db
                    ->where('sonod_uniq_id_gensss', $id)
                    ->get('warish_somod_person')
                    ->result();
    }

    public function get_dead_person($uniq)
    {
        return $this->db
                    ->where('sonodss_uniqss', $uniq)
                    ->join('villages_of_union', 'villages_of_union.villages_of_union_a_iidd = warish_person_infos.gramer_namer_id', 'left')
                    ->join('house_home_name_etc', 'house_home_name_etc.house_home_name_etc_iddiddd = warish_person_infos.barir_namer_id', 'left')
                    ->get('warish_person_infos')
                    ->row();
    }

    /**
     * Get all generated sonods for a specific tenant parishad.
     *
     * @param int $tenant_id
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get_all_generated_sonods_by_tenant($tenant_id, $limit = 500, $offset = 0)
    {
        return $this->db
            ->select('sonod_genarates.*, cp.ver_per_info_name_bn, cp.ver_per_info_name_en, cp.ver_per_info_father_name_bn, cp.ver_per_info_ward_no, cp.ver_per_info_verify_type, cp.ver_per_nid_autopr_ididi, cp.tim_es_tmp, u.username as issuer_username')
            ->from('sonod_genarates')
            ->join('certificate_person_verify_info_editing cp', 'cp.ver_per_info_sonod_uniqs = sonod_genarates.sonod_uniqs', 'left')
            ->join('users u', 'u.id = sonod_genarates.gen_usr_ididd', 'left')
            ->where('sonod_genarates.insididid_pridididid', (int)$tenant_id)
            ->order_by('sonod_genarates.sonod_gen_ididid', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->result();
    }

    /**
     * Count all generated sonods for a specific tenant parishad.
     *
     * @param int $tenant_id
     * @return int
     */
    public function count_all_generated_sonods_by_tenant($tenant_id)
    {
        return $this->db
            ->where('insididid_pridididid', (int)$tenant_id)
            ->count_all_results('sonod_genarates');
    }

    /**
     * Save or update a certificate draft for a tenant parishad.
     *
     * @param int $tenant_id
     * @param int $user_id
     * @param array $data
     * @return int
     */
    public function save_draft($tenant_id, $user_id, array $data)
    {
        $draft_id = (int)($data['id'] ?? 0);
        $payload = array(
            'ins_id'             => (int)$tenant_id,
            'user_id'            => (int)$user_id,
            'step_number'        => (int)($data['step_number'] ?? 1),
            'draft_title'        => $data['draft_title'] ?? '',
            'applicant_name'     => $data['applicant_name'] ?? '',
            'applicant_nid_brn'  => $data['applicant_nid_brn'] ?? '',
            'sonod_type_id'      => $data['sonod_type_id'] ?? '',
            'payload_json'       => is_string($data['payload_json']) ? $data['payload_json'] : json_encode($data['payload_json']),
            'updated_at'         => time(),
        );

        if ($draft_id > 0) {
            $existing = $this->get_draft_by_id($draft_id, $tenant_id);
            if ($existing) {
                $this->db->where('id', $draft_id)->where('ins_id', (int)$tenant_id)->update('sonod_drafts', $payload);
                return $draft_id;
            }
        }

        $payload['created_at'] = time();
        $this->db->insert('sonod_drafts', $payload);
        return (int)$this->db->insert_id();
    }

    /**
     * Get a draft by ID scoped to tenant.
     *
     * @param int $id
     * @param int $tenant_id
     * @return object|null
     */
    public function get_draft_by_id($id, $tenant_id)
    {
        return $this->db
            ->where('id', (int)$id)
            ->where('ins_id', (int)$tenant_id)
            ->get('sonod_drafts')
            ->row();
    }

    /**
     * Get all drafts for a tenant.
     *
     * @param int $tenant_id
     * @param int $limit
     * @return array
     */
    public function get_tenant_drafts($tenant_id, $limit = 100)
    {
        return $this->db
            ->select('sonod_drafts.*, u.username as creator_username')
            ->from('sonod_drafts')
            ->join('users u', 'u.id = sonod_drafts.user_id', 'left')
            ->where('sonod_drafts.ins_id', (int)$tenant_id)
            ->order_by('sonod_drafts.updated_at', 'DESC')
            ->limit((int)$limit)
            ->get()
            ->result();
    }

    /**
     * Delete a draft scoped to tenant.
     *
     * @param int $id
     * @param int $tenant_id
     * @return bool
     */
    public function delete_draft($id, $tenant_id)
    {
        return $this->db
            ->where('id', (int)$id)
            ->where('ins_id', (int)$tenant_id)
            ->delete('sonod_drafts');
    }

    /**
     * Fetch issued sonod details for edit scoped to tenant.
     *
     * @param string|int $id_or_uniq
     * @param int $tenant_id
     * @return object|null
     */
    public function get_issued_sonod_for_edit($id_or_uniq, $tenant_id)
    {
        $this->db
            ->select('sonod_genarates.*, cp.*, u.username as issuer_username')
            ->from('sonod_genarates')
            ->join('certificate_person_verify_info_editing cp', 'cp.ver_per_info_sonod_uniqs = sonod_genarates.sonod_uniqs', 'left')
            ->join('users u', 'u.id = sonod_genarates.gen_usr_ididd', 'left')
            ->where('sonod_genarates.insididid_pridididid', (int)$tenant_id);

        if (is_numeric($id_or_uniq)) {
            $this->db->group_start()
                ->where('sonod_genarates.sonod_gen_ididid', (int)$id_or_uniq)
                ->or_where('sonod_genarates.sonod_uniqs', (string)$id_or_uniq)
                ->or_where('sonod_genarates.cgp_pr_idiidid_pr', (int)$id_or_uniq)
                ->group_end();
        } else {
            $this->db->where('sonod_genarates.sonod_uniqs', (string)$id_or_uniq);
        }

        return $this->db->get()->row();
    }

    /**
     * Fetch warish records for a specific sonod uniq id.
     *
     * @param string $sonod_uniq
     * @return array
     */
    public function get_warish_persons_by_sonod_uniq($sonod_uniq)
    {
        if (empty($sonod_uniq)) {
            return array();
        }
        return $this->db
            ->where('sonod_uniq_id_gensss', (string)$sonod_uniq)
            ->order_by('warish_somod_person_iddd', 'ASC')
            ->get('warish_somod_person')
            ->result();
    }

    /**
     * Check if an approved edit permission exists for this sonod.
     *
     * @param int|string $sonod_id
     * @param string $sonod_uniq
     * @param int $tenant_id
     * @return bool
     */
    public function check_approved_sonod_edit_permission($sonod_id, $sonod_uniq, $tenant_id)
    {
        $sql = "SELECT id FROM approvals 
                WHERE ins_pr_id_s = ? 
                  AND action = 'sonod.edit' 
                  AND status = 'approved' 
                  AND (payload->>'sonod_id' = ? OR payload->>'sonod_uniqs' = ?)
                ORDER BY id DESC LIMIT 1";
        $row = $this->db->query($sql, array((int)$tenant_id, (string)$sonod_id, (string)$sonod_uniq))->row();
        return !empty($row);
    }

    /**
     * Check if a pending edit permission exists for this sonod.
     *
     * @param int|string $sonod_id
     * @param string $sonod_uniq
     * @param int $tenant_id
     * @return object|null
     */
    public function get_pending_sonod_edit_permission($sonod_id, $sonod_uniq, $tenant_id)
    {
        $sql = "SELECT * FROM approvals 
                WHERE ins_pr_id_s = ? 
                  AND action = 'sonod.edit' 
                  AND status = 'pending' 
                  AND (payload->>'sonod_id' = ? OR payload->>'sonod_uniqs' = ?)
                ORDER BY id DESC LIMIT 1";
        return $this->db->query($sql, array((int)$tenant_id, (string)$sonod_id, (string)$sonod_uniq))->row();
    }

    /**
     * Update sonod_genarates row for a tenant.
     *
     * @param string $sonod_uniq
     * @param int $tenant_id
     * @param array $data
     * @return bool
     */
    public function update_issued_sonod_gen($sonod_uniq, $tenant_id, $data)
    {
        return $this->db
            ->where('sonod_uniqs', (string)$sonod_uniq)
            ->where('insididid_pridididid', (int)$tenant_id)
            ->update('sonod_genarates', $data);
    }

    /**
     * Update or insert person verification details for a sonod uniq.
     *
     * @param string $sonod_uniq
     * @param array $data
     * @return bool
     */
    public function update_issued_sonod_person_info($sonod_uniq, $data)
    {
        $existing = $this->db->where('ver_per_info_sonod_uniqs', (string)$sonod_uniq)->get('certificate_person_verify_info_editing')->row();
        if ($existing) {
            return $this->db->where('ver_per_info_sonod_uniqs', (string)$sonod_uniq)->update('certificate_person_verify_info_editing', $data);
        } else {
            $data['ver_per_info_sonod_uniqs'] = (string)$sonod_uniq;
            $data['tim_es_tmp'] = (string)time();
            return $this->db->insert('certificate_person_verify_info_editing', $data);
        }
    }

    /**
     * Update warish list for a sonod uniq.
     *
     * @param string $sonod_uniq
     * @param array $warish_rows
     * @return bool
     */
    public function update_warish_somod_persons($sonod_uniq, $warish_rows)
    {
        if (empty($sonod_uniq)) {
            return FALSE;
        }
        $this->db->where('sonod_uniq_id_gensss', (string)$sonod_uniq)->delete('warish_somod_person');
        if (!empty($warish_rows) && is_array($warish_rows)) {
            $this->db->insert_batch('warish_somod_person', $warish_rows);
        }
        return TRUE;
    }

    /**
     * Fetch trade license specific table data if available
     *
     * @param string $sonod_uniq
     * @return object|null
     */
    public function get_trade_license_data($sonod_uniq)
    {
        if (empty($sonod_uniq)) {
            return null;
        }
        return $this->db
                    ->where('sonod_uniq_id', (string)$sonod_uniq)
                    ->get('tread_license_tbl_data')
                    ->row();
    }

    public function insert_trade_license_data($data)
    {
        $this->db->insert('tread_license_tbl_data', $data);
        return $this->db->insert_id();
    }

    public function update_trade_license_data($sonod_uniq, $data)
    {
        if (empty($sonod_uniq)) {
            return FALSE;
        }
        $existing = $this->get_trade_license_data($sonod_uniq);
        if ($existing) {
            $this->db->where('sonod_uniq_id', (string)$sonod_uniq)->update('tread_license_tbl_data', $data);
            return TRUE;
        } else {
            $data['sonod_uniq_id'] = (string)$sonod_uniq;
            $this->db->insert('tread_license_tbl_data', $data);
            return $this->db->insert_id();
        }
    }
}





