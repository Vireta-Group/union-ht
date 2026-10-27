<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

class Getapi
{
    /**
     * Verify NID / Birth Registration Number.
     *
     * 1. Checks the current tenant's local database (family_data_sheet & family_member).
     * 2. If not found locally, queries online verification endpoint if configured (APP_NID_API_URL).
     * 3. If online API is not configured or returns nothing, returns clean empty fields for manual entry
     *    and records the verification entry in person_verify_nid_data_info_saving so a valid lastID is preserved.
     */
    public function m_api_data($nid, $dob, $user_uniq = null)
    {
        $CI =& get_instance();

        $CI->load->model('SettingModel');
        $CI->load->model('FamilyModel');
        $CI->load->model('SonodCrudModel', 'sonodM');

        $clean_num = trim((string)$nid);
        $clean_dob = trim((string)$dob);
        $tenant_id = (int)tenant_code();

        // -------------------------------------------------------------------
        // 1. Check local Union Parishad citizen records
        // -------------------------------------------------------------------
        if ($clean_num !== '') {
            // Check primary family heads (family_data_sheet)
            $sheet = $CI->db->where('ins_aut_iiid', $tenant_id)
                            ->where('nid_number', $clean_num)
                            ->get('family_data_sheet')
                            ->row();

            if ($sheet) {
                $v_id = $this->record_person_verify(
                    $sheet->family_person_name_head,
                    '',
                    $sheet->pason_father_name,
                    $sheet->parson_mother_name,
                    $clean_num,
                    $sheet->date_of_birth ?: $clean_dob,
                    $sheet->word_number,
                    'local_holding_sheet'
                );

                return json_encode(array(
                    'lastID'    => $v_id,
                    'nameBn'    => $sheet->family_person_name_head ?: '',
                    'nameEn'    => '',
                    'fatheBn'   => $sheet->pason_father_name ?: '',
                    'fatherEN'  => '',
                    'motherBn'  => $sheet->parson_mother_name ?: '',
                    'motherEN'  => '',
                    'ward'      => $sheet->word_number ?: '',
                    'villid'    => (int)($sheet->vill_pr_iddsid ?: 0),
                    'homeid'    => (int)($sheet->house_etc_a_iddd ?: 0),
                    'dob'       => $sheet->date_of_birth ?: $clean_dob,
                    '_source'   => 'local_database'
                ));
            }

            // Check family members (family_member)
            $member = $CI->db->where('in_s_pr_id_dd', $tenant_id)
                             ->where('nid_bdris', $clean_num)
                             ->get('family_member')
                             ->row();

            if ($member) {
                $ward = '';
                $villid = 0;
                $homeid = 0;
                if (!empty($member->family_datasid)) {
                    $p_sheet = $CI->db->where('data_id', $member->family_datasid)->get('family_data_sheet')->row();
                    if ($p_sheet) {
                        $ward   = $p_sheet->word_number ?: '';
                        $villid = (int)($p_sheet->vill_pr_iddsid ?: 0);
                        $homeid = (int)($p_sheet->house_etc_a_iddd ?: 0);
                    }
                }

                $v_id = $this->record_person_verify(
                    $member->member_name,
                    '',
                    $member->father_name,
                    $member->mother_name,
                    $clean_num,
                    $member->date_of_birth ?: $clean_dob,
                    $ward,
                    'local_family_member'
                );

                return json_encode(array(
                    'lastID'    => $v_id,
                    'nameBn'    => $member->member_name ?: '',
                    'nameEn'    => '',
                    'fatheBn'   => $member->father_name ?: '',
                    'fatherEN'  => '',
                    'motherBn'  => $member->mother_name ?: '',
                    'motherEN'  => '',
                    'ward'      => $ward,
                    'villid'    => $villid,
                    'homeid'    => $homeid,
                    'dob'       => $member->date_of_birth ?: $clean_dob,
                    '_source'   => 'local_database'
                ));
            }
        }

        // -------------------------------------------------------------------
        // 2. Query online verification endpoint if configured
        // -------------------------------------------------------------------
        $url_path = trim((string) getenv('APP_NID_API_URL'));

        if ($url_path !== '') {
            $token = base64_encode(strrev(base64_encode($user_uniq ?: 'user')).'-'.strrev(base64_encode(strtotime("+1 day"))));

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("token: " . $token));
            curl_setopt($curl, CURLOPT_URL, $url_path);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, array('nid_no' => $clean_num, 'dob' => $clean_dob));
            curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($curl, CURLOPT_TIMEOUT, 12);

            $output = curl_exec($curl);
            $err    = curl_error($curl);
            curl_close($curl);

            if ($output !== false && $output !== '') {
                $decoded = json_decode($output, true);
                if (is_array($decoded) && (!empty($decoded['nameBn']) || !empty($decoded['nameEn']))) {
                    $v_id = $this->record_person_verify(
                        $decoded['nameBn'] ?? '',
                        $decoded['nameEn'] ?? '',
                        $decoded['fatheBn'] ?? '',
                        $decoded['motherBn'] ?? '',
                        $clean_num,
                        $clean_dob,
                        '',
                        'online_api'
                    );
                    $decoded['lastID'] = $v_id;
                    return json_encode($decoded);
                }
            }
        }

        // -------------------------------------------------------------------
        // 3. Fallback: No external API or not found -> Prepare clean manual entry
        // DO NOT inject dummy mock data ("Mock Citizen", "পরীক্ষামূলক নাগরিক")!
        // -------------------------------------------------------------------
        $v_id = $this->record_person_verify('', '', '', '', $clean_num, $clean_dob, '', 'manual_entry');

        return json_encode(array(
            'lastID'   => $v_id,
            'nameBn'   => '',
            'nameEn'   => '',
            'fatheBn'  => '',
            'fatherEN' => '',
            'motherBn' => '',
            'motherEN' => '',
            '_manual'  => true,
            '_note'    => 'ম্যানুয়াল এন্ট্রি প্রস্তুত।'
        ));
    }

    /**
     * Record verification entry into person_verify_nid_data_info_saving.
     * Ensures an integer person_v_id exists and stores the citizen's NID/BRN and DOB.
     */
    private function record_person_verify($name_bn, $name_en, $f_name_bn, $m_name_bn, $nid, $dob, $ward = '', $thro = 'manual')
    {
        $CI =& get_instance();
        $payload = array(
            'name_bn'        => $name_bn ?: '',
            'name_en'        => $name_en ?: '',
            'f_name_bn'      => $f_name_bn ?: '',
            'm_name_bn'      => $m_name_bn ?: '',
            'dob_get'        => $dob ?: '',
            'ins_wardno'     => $ward ?: '',
            'verify_ins_ids' => (string)tenant_code(),
            'verify_thro'    => $thro,
            'verify_times'   => (string)time(),
            'verify_dates'   => date('Y-m-d'),
            'verify_user_id' => (int)($CI->session->userdata('user_id') ?: 1)
        );

        $clean = trim((string)$nid);
        if (strlen($clean) == 10) {
            $payload['nid_num10'] = $clean;
        } else if ($clean !== '') {
            $payload['nid_no17']  = $clean;
        }

        $CI->db->insert('person_verify_nid_data_info_saving', $payload);
        return (int)$CI->db->insert_id();
    }

    public function porichoy_nid_api_data()
    {
        # code...
    }

    public function porichoy_birth_api_data()
    {
        # code...
    }

    public function get_main_api_data($nid, $dob)
    {
        return $this->m_api_data($nid, $dob);
    }
}
