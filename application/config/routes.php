<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'front';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/* Main route */
$route['up'] 						= 'front/up';
$route['landing'] 					= 'front/landing';
$route['about'] 					= 'front/about';
$route['plans'] 					= 'front/plans';
$route['contact'] 					= 'front/contact';
$route['reviews'] 					= 'front/reviews';
$route['api/reviews/submit'] 			= 'front/api_submit_review';
$route['api_submit_review'] 			= 'front/api_submit_review';
$route['api/reviews/public'] 			= 'front/api_public_reviews';
$route['sadmin_reviews/(:any)'] 		= 'sadmin_reviews/$1';
$route['sadmin_reviews'] 				= 'sadmin_reviews/api_list';
$route['faq'] 						= 'front/faq';
$route['policy'] 					= 'front/policy';
$route['privacy'] 					= 'front/privacy';
$route['terms'] 					= 'front/terms';
$route['register'] 					= 'front/register';
/* ---------------------------------------------------------------------------
 * Super-admin panel.
 *
 * /super_admin is the OVERVIEW. The tenant directory and its three APIs
 * moved to sadmin_tenants/ when the panel grew from one page to seven.
 * The old super_admin/api_* routes are kept as aliases so any URL already
 * in a bookmark, a cached page or a support answer still resolves — a
 * 500 on super_admin/api_toggle_domain is not a way to learn a panel moved.
 * --------------------------------------------------------------------------- */
$route['super_admin']                  = 'super_admin/index';
$route['sadmin']                       = 'super_admin/index';
$route['super_admin/switch/(:num)']   = 'super_admin/switch_tenant/$1';

$route['sadmin_tenants']                       = 'sadmin_tenants/index';
$route['sadmin_tenants/api_update_tenant']    = 'sadmin_tenants/api_update_tenant';
$route['sadmin_tenants/api_create_tenant']    = 'sadmin_tenants/api_create_tenant';
$route['sadmin_tenants/api_toggle_domain']    = 'sadmin_tenants/api_toggle_domain';
$route['sadmin_tenants/api_toggle_active']    = 'sadmin_tenants/api_toggle_tenant_active';
$route['sadmin_tenants/api_approve_tenant']   = 'sadmin_tenants/api_approve_tenant';
$route['sadmin_tenants/api_reject_tenant']    = 'sadmin_tenants/api_reject_tenant';
$route['sadmin_tenants/api_create_temp_admin'] = 'sadmin_tenants/api_create_temp_admin';
$route['sadmin_tenants/api_revoke_temp_admin'] = 'sadmin_tenants/api_revoke_temp_admin';
$route['sadmin_tenants/api_list_temp_admins']   = 'sadmin_tenants/api_list_temp_admins';
$route['super_admin/api_update_tenant']        = 'sadmin_tenants/api_update_tenant';
$route['super_admin/api_create_tenant']        = 'sadmin_tenants/api_create_tenant';
$route['super_admin/api_toggle_domain']        = 'sadmin_tenants/api_toggle_domain';

$route['sadmin_subscriptions']                    = 'sadmin_subscriptions/index';
$route['sadmin_subscriptions/api_assign']         = 'sadmin_subscriptions/api_assign';
$route['sadmin_subscriptions/api_renew']          = 'sadmin_subscriptions/api_renew';
$route['sadmin_subscriptions/api_set_dates']      = 'sadmin_subscriptions/api_set_dates';
$route['sadmin_subscriptions/api_cancel']         = 'sadmin_subscriptions/api_cancel';
$route['sadmin_subscriptions/api_save_package']   = 'sadmin_subscriptions/api_save_package';
$route['sadmin_subscriptions/api_add_payment']    = 'sadmin_subscriptions/api_add_payment';
$route['sadmin_subscriptions/api_delete_payment'] = 'sadmin_subscriptions/api_delete_payment';

/* NOT BUILT. Every route below resolves to a controller that does not exist,
 * so all six answer CI3's 404 page. They are declared rather than deleted on
 * purpose: a declared-and-absent route is a loud 404, whereas a deleted
 * route leaves nothing recording that the module was planned and why it
 * stopped. See docs/SADMIN-PANEL.md section 6.
 *
 * Every one of the five api_* entries is a write to `users` or
 * `users_groups`, which needs explicit approval. The read half (this page)
 * does not, and is the next thing to build. Do not add the write endpoints
 * because the read page exists. */
$route['sadmin_users']                    = 'sadmin_users/index';
$route['sadmin_users/api_save_user']      = 'sadmin_users/api_save_user';
$route['sadmin_users/api_toggle_active']  = 'sadmin_users/api_toggle_active';
$route['sadmin_users/api_reset_password'] = 'sadmin_users/api_reset_password';
$route['sadmin_users/api_delete_user']    = 'sadmin_users/api_delete_user';
$route['sadmin_users/api_set_group']      = 'sadmin_users/api_set_group';

/* Super Admin Sonod Global Design & Template Management */
$route['sadmin_sonod']                              = 'sadmin_sonod/index';
$route['sadmin_sonod/save']                         = 'sadmin_sonod/save';
$route['sadmin_sonod/save_global_sonod_text']       = 'sadmin_sonod/save_global_sonod_text';
$route['sadmin_sonod/save_tenant_sonod_override']   = 'sadmin_sonod/save_tenant_sonod_override';
$route['sadmin_sonod/reset_tenant_sonod_override']  = 'sadmin_sonod/reset_tenant_sonod_override';
$route['sadmin_sonod/api_get_sonods']               = 'sadmin_sonod/api_get_sonods';
$route['sadmin_sonod/api_get_config']               = 'sadmin_sonod/api_get_config';
$route['sadmin_sonod/api_save_config']              = 'sadmin_sonod/api_save_config';

$route['sadmin_support/stream'] = 'support_sse/stream';
$route['sadmin_support/live']   = 'support_sse/probe';
$route['sadmin_support']                   = 'sadmin_support/index';
$route['sadmin_support/ticket/(:num)']     = 'sadmin_support/ticket/$1';
$route['sadmin_support/api_reply']         = 'sadmin_support/api_reply';
$route['sadmin_support/api_set_status']    = 'sadmin_support/api_set_status';
$route['sadmin_support/api_delete']        = 'sadmin_support/api_delete_ticket';

/* Public support intake & live chat endpoints. NOT under sadmin_support/ on purpose: the
 * Support controller carries no sadmin gate, because a parishad or public visitor submitting a
 * ticket/chat is by definition not a platform super admin. */
$route['support_submit']                   = 'support/submit';
$route['support/start_chat']               = 'support/start_chat';
$route['support/send_message']             = 'support/send_message';
$route['support/history']                  = 'support/history';
$route['support/live_stream']              = 'support/live_stream';

$route['sadmin_settings']                    = 'sadmin_settings/index';
$route['sadmin_settings/api_save_platform']  = 'sadmin_settings/api_save_platform';
$route['sadmin_settings/api_save_tenant']    = 'sadmin_settings/api_save_tenant';
$route['sadmin_settings/api_get_tenant']     = 'sadmin_settings/api_get_tenant';
/* The one panel endpoint that is NOT JSON. A file cannot be sent in a JSON
 * body without base64-inlining it, so this one is a multipart form post.
 * The other three in this group are fetch() with a JSON body. */
$route['sadmin_settings/api_upload_asset']   = 'sadmin_settings/api_upload_asset';

$route['sadmin_audit']                      = 'sadmin_audit/index';
$route['sadmin_audit/export']               = 'sadmin_audit/export';
$route['sadmin_audit/api_purge_before']     = 'sadmin_audit/api_purge_before';
$route['api_register_tenant'] 		= 'front/api_register_tenant';
$route['api_login'] 				= 'front/api_login';
$route['login'] 					= 'front/login';
$route['forgot_password']           = 'auth/forgot_password';
$route['reset_password/(:any)']     = 'auth/reset_password/$1';
$route['add'] 					    = 'front/adds';
$route['search_hold'] 				= 'front/search_holding_front_page';
$route['src_holding'] 				= 'front/search_holding_for_page_api';
$route['insrt'] 					= 'front/insert_data';
$route['getHomeByVill']             = 'front/get_house_by_village_nos';
$route['frontGetVillByWard']        = 'front/get_village_by_ward_nos';
$route['addNewHomeFront']           = 'front/add_new_housess';
$route['singleUser']                = 'front/single_membs_view_methods';
$route['singleEmp']                 = 'front/single_emp_view_method';
$route['pub_view/(:any)']           = 'front/single_holding_person_methods/$1';
$route['sonod-v']                   = 'front/publicly_single_sonod_verify_method';
$route['sonod-v/(:any)']            = 'front/publicly_single_sonod_verify_method/$1';
$route['sonod_verify']              = 'front/publicly_single_sonod_verify_method';
$route['sonod_verify/(:any)']       = 'front/publicly_single_sonod_verify_method/$1';

$route['login_check'] 				= 'auth/login';
$route['logout'] 					= 'auth/logout';

// Dashboard Controller

$route['view_data_doc'] 			= 'dashboard/view_full_data_sheets_doc';


$route['dashboard_u'] 				= 'dashboard/mdash'; 
$route['insert_family_user_data'] 	= 'dashboard/insert_data_sheet_form_datas';
$route['addData'] 					= 'dashboard/dataset_form';
$route['dataEntrySuccess']          = 'dashboard/success_entry_data_sheets';
$route['nid_v'] 			        = 'dashboard/nid_verify_getting_datas';
$route['taxCollection'] 			= 'dashboard/tax_collections_view';
$route['searching_tax']             = 'dashboard/search_for_tax_collect';
$route['slip_print']                = 'dashboard/print_invoice_slip';
$route['view'] 			            = 'dashboard/single_entry_view';
$route['updateDataSheet']           = 'dashboard/update_data_sheet_by_id';
$route['searchHoldingss']           = 'dashboard/search_holdings_view_file_ss';
$route['search_holding_info']       = 'dashboard/search_holdings_view_file_ss';
$route['view_datas'] 			    = 'dashboard/view_full_data_sheetss';
$route['getDataByWardNo'] 			= 'dashboard/get_data_by_ward_json_encode';
$route['downloadWardDoc'] 		    = 'dashboard/download_ward_doc';
$route['getHoldingByNum'] 			= 'dashboard/get_holding_by_noss';
$route['home_view']                 = 'dashboard/house_name_view';
$route['receipt_show'] 			    = 'dashboard/search_invoice_receipt_shows';
$route['getInvcInfo']               = 'dashboard/search_show_receipt_s';
$route['getInvcInfoHolding']        = 'dashboard/search_show_receipt_byHoldings';
$route['userManagement'] 			= 'dashboard/user_lists_view';
$route['getInsUsers'] 		        = 'dashboard/get_this_ins_users_list';
$route['userChangeActivity']        = 'dashboard/change_users_activity';
$route['changeUserRole']            = 'dashboard/change_user_role';
$route['entryNewUser']              = 'dashboard/entry_new_user_for_this_ins';
$route['userView']                  = 'dashboard/check_user_profile';
$route['userPassChangeAdm']         = 'dashboard/admin_change_user_password';
$route['my_profile'] 			    = 'dashboard/user_profile_view';
$route['plates_set'] 			    = 'dashboard/plate_system_settings_fun';
$route['sys_set'] 			        = 'dashboard/system_settings_fun';
$route['update_sets'] 			    = 'dashboard/update_system_setting';
$route['approvals'] 			    = 'dashboard/approvals_view';
$route['approval_action'] 		    = 'dashboard/handle_approval_action';
$route['subscription']                  = 'dashboard/subscription_view';
$route['subscription/submit_payment']   = 'dashboard/submit_subscription_payment';
$route['subscription/change_package']   = 'dashboard/change_subscription_package';
$route['subscription/invoice/(:any)']   = 'dashboard/subscription_invoice/$1';
$route['renew_plan']                    = 'dashboard/subscription_view';
$route['support']                       = 'dashboard/support_view';
$route['support/create']                = 'dashboard/api_create_support_ticket';
$route['support/reply']                 = 'dashboard/api_reply_support_ticket';
$route['support/details/(:num)']        = 'dashboard/api_get_ticket_details/$1';
$route['update_profile'] 			= 'dashboard/update_user_profile';
$route['change_pass'] 			    = 'dashboard/change_user_self_passwords';
$route['chair_man_sign'] 			= 'dashboard/change_chair_man_sign';
$route['update_man_sign'] 			= 'dashboard/update_chairMan_signs';
$route['update_man_picture'] 		= 'dashboard/update_chairMan_pic_tures';
$route['verify_pass'] 			    = 'dashboard/self_pass_verify_';
$route['emp_view'] 			        = 'dashboard/employee_manages_view_func';
$route['getInsEmployees'] 			= 'dashboard/get_this_institute_employee';
$route['insertEmp'] 			    = 'dashboard/insert_this_institute_new_employee';
$route['getEmpSingle'] 			    = 'dashboard/get_single_employee_by_id';
$route['delInsEmp'] 			    = 'dashboard/delete_single_employee_by_id';
$route['upMem'] 			        = 'dashboard/up_members_view';
$route['getInsUpMember'] 			= 'dashboard/get_this_ins_up_members';
$route['insertMem'] 			    = 'dashboard/insert_this_institute_new_up_member';
$route['getMemSingle'] 			    = 'dashboard/get_single_member_by_id';
$route['updateUpMember'] 			= 'dashboard/update_this_institute_edit_up_member';
$route['delInsMem'] 			    = 'dashboard/delete_single_member_by_id';
$route['cashDeposite'] 			    = 'dashboard/deposite_user_cash_view_method';
$route['editThisUserAdmn'] 			= 'dashboard/editingThisUserByUniqID';
$route['dueCollect'] 			    = 'dashboard/collect_from_user_duess';
$route['userCollectHistory'] 		= 'dashboard/user_collected_history_by_date_to_date';
$route['userPaidHistory'] 		    = 'dashboard/user_paid_infos_by_datetodate';
$route['depositeDueAmount'] 		= 'dashboard/deposite_self_cash_without_commission';
$route['approveDeposite'] 		    = 'dashboard/approve_deposite_by_id';
$route['listDepositeS'] 		    = 'dashboard/all_deposite_list';
$route['pendingList'] 		        = 'dashboard/all_pending_deposite_list';
$route['reports'] 		            = 'dashboard/report_method';
$route['AllCollectreport'] 		    = 'dashboard/get_all_collect_date_to_date';
$route['AllPaidReport'] 		    = 'dashboard/get_all_paid_date_to_date';
$route['sliderOption'] 		        = 'dashboard/slider_manages_methods';
$route['getSldr'] 		            = 'dashboard/get_all_slider_json_methods';
$route['insertNewSlider'] 		    = 'dashboard/insert_slider_manage';
$route['deleteThisSlider'] 		    = 'dashboard/deleted_this_slider_ss';
$route['balanceQuery'] 		        = 'dashboard/institute_present_this_balance';

// un used
$route['family_mem_insert']         = 'dashboard/insert_family_data_sheet_form_datas';
$route['searching']                 = 'dashboard/search_all_data_searching';
$route['getAll_family_member'] 	    = 'dashboard/get_family_member';
$route['delete_selected_members']   = 'dashboard/delete_member_by_id';
$route['taxs_paid']                 = 'dashboard/insert_tax_paids';
$route['get_dataSingle']            = 'dashboard/get_data_by_id';
$route['getHomesbyWard']            = 'dashboard/get_homess_show_json_byward';
$route['getVillByWard']             = 'dashboard/get_village_by_ward_nos';
$route['addNewHome']                = 'dashboard/add_new_housess';
$route['editHomeName']              = 'dashboard/edit_home_name_by_a_idd';
$route['getHouseByVillage']         = 'dashboard/get_house_by_village_nos';
$route['getHoldingByHouse']         = 'dashboard/get_holding_info_by_house_no';
/* Test */
$route['ck']                        = 'dashboard/fun';

$route['apps_api']                  = 'api/sample/apps_collection';
$route['apps_login']                = 'api/login';
$route['app_ck']                    = 'api/login/login';
$route['slip']                      = 'api/sample/print_slip';
$route['ap_logout']                 = 'api/login/logout';

// Download Controller
$route['fullPlateWard']             = 'download/s_plate_by_ward';
$route['recipt_print']              = 'download/print_recite_forms';
$route['psonod']                    = 'download/sonod_with_pad';
$route['nsonod']                    = 'download/sonod_without_pad';
$route['export_all_global_showcase']= 'download/export_all_global_showcase';



// Sonod Controller
$route['addGSonod']                 = 'sonod/insert_global_sonods';
$route['getSingleSonod']            = 'sonod/get_global_single_sonod_by_id';
$route['addMySonod']                = 'sonod/add_sonod_self_union_ins_';
$route['newSonod']                  = 'sonod/sonod_genaret_method_view';
$route['nid_v']                     = 'sonod/verify_this_sonod_person_nid_infos';
$route['insertNewSond']             = 'sonod/insert_new_sonod_details_persons';
$route['eGSonod']                   = 'sonod/edit_global_sonods_view_func_opt';
$route['new_warish_sonod_create']   = 'sonod/insert_new_warish_sonod_created_func';
$route['new_trade_license_create']  = 'sonod/insert_new_trade_license_created_func';
$route['sonod_list']                = 'sonod/sonod_list_view';
$route['saveMyCustomSonod']         = 'sonod/save_tenant_custom_sonod';
$route['saveEditSonod']             = 'sonod/save_tenant_custom_sonod';
$route['deleteMyCustomSonod']       = 'sonod/delete_tenant_custom_sonod';
$route['getMyCustomSonod']          = 'sonod/get_tenant_single_custom_sonod';
$route['sonodTheme']                = 'sonod/sonod_theme_settings_view';
$route['saveSonodTheme']            = 'sonod/save_sonod_theme_settings';
$route['saveSonodDraft']            = 'sonod/save_server_draft';
$route['getSonodDraft']             = 'sonod/get_server_draft';
$route['deleteSonodDraft']          = 'sonod/delete_server_draft';
$route['sonodDrafts']               = 'sonod/drafts_list_view';
$route['editIssuedSonod']            = 'sonod/edit_issued_sonod';
$route['updateIssuedSonod']          = 'sonod/update_issued_sonod';
$route['requestSonodEditPermission'] = 'sonod/request_edit_permission';
$route['approveSonodEditPermission'] = 'sonod/approve_edit_permission_inline';
$route['sonod_audit']               = 'sonod/audit_logs_view';
$route['sonod_audit/details/(:num)'] = 'sonod/audit_log_details_api/$1';






