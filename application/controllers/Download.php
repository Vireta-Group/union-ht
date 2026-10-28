<?php
defined('BASEPATH') OR exit('No direct script access allowed');


use tecnickcom\tcpdf\tcpdf_barcodes_2d;
use tecnickcom\tcpdf\tcpdf_barcodes_1d;

// use tecnickcom\tcpdf\tcpdf;


// $conv = new \Anam\PhantomMagick\Converter();
// $conv->source('http://google.com')
//     ->toPng()
//     ->save('/your/destination/path/google.png');




class CustomLanguageToFontImplementation extends \Mpdf\Language\LanguageToFont
{

    public function getLanguageOptions($llcc, $adobeCJK)
    {
        $tags = explode('-', $llcc);
        $lang = strtolower($tags[0]);
        if (in_array($lang, ['bn', 'ben', 'bd'], true)) {
            return [false, 'solaimanlipi'];
        }
        if ($llcc === 'th') {
            return [false, 'solaimanlipi']; 
        }

        return parent::getLanguageOptions($llcc, $adobeCJK);
    }

}





class BanglaConverter {

    public static $bn = array("১", "২", "৩", "৪", "৫", "৬", "৭", "৮", "৯", "০");
    public static $en = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "0");
    
    public static function bn2en($number) {
        return str_replace(self::$bn, self::$en, $number);
    }
    
    public static function en2bn($number) {
        return str_replace(self::$en, self::$bn, $number);
    }

	public static function base64ToImage($base64_string, $output_file) {
		$file = fopen($output_file, "wb");
	
		$data = explode(',', $base64_string);
	
		fwrite($file, base64_decode($data[1]));
		fclose($file);
	
		return $output_file;
	}
    
}




class Download extends CI_Controller {

	public function __construct()
	{
		parent::__construct();

		$this->load->library('template');
		$this->load->library('Ion_auth');
		$this->load->library('session');
		$this->load->library('form_validation');
		$this->load->library('upload');
		$this->load->library('user_agent');
		$this->load->library('curl');
		$this->load->helper('url');

		$this->load->model('ion_auth_model');
		$this->load->model('UserModel');
		$this->load->model('SettingModel');
		$this->load->model('AddMemberModel');
		$this->load->model('FamilyModel');
		$this->load->model('SonodCrudModel', 'sonodM');

		if ($this->ion_auth->logged_in()) {
			$this->user_id = $this->ion_auth->user()->row()->user_full_tbl_id;
			$this->data['loggeduser_info'] = $this->UserModel->getUserInfo($this->user_id);

			// Tenant guard — see enforce_tenant_scope(). Only reached for a
			// signed-in user; the helper returns quietly for anonymous callers.
			//
			// NOTE, and this is not fixed here: this constructor has NO auth gate
			// at all — it sets the user if there is one and otherwise carries on.
			// So fullPlateWard, recipt_print, psonod and nsonod are reachable
			// anonymously. Checked 2026-09-29 with a real collect_amnt_invoice_idd
			// and they return an empty form, no tenant rows — so it is a latent
			// risk rather than a live leak. Whether these documents are meant to
			// be public is a product question, not a bug fix, so the gate was left
			// alone rather than guessed at.
			enforce_tenant_scope();
			enforce_tenant_status();
		}

		$this->data['ins_info'] = $this->SettingModel->getThisInsName();

	}

	public function index()
	{
		$this->template->front('', $this->data);
	}

	public function s_platev()
	{
        // require_once('k.php');

		$uniq_id = $this->input->get('uniq');

		$this->data['main_data'] = $this->FamilyModel->serach_data_by_uniq_num($uniq_id); 

		$this->data['member_s'] = $this->FamilyModel->serach_up_member($this->data['main_data']->word_number); 

		
		// barcode Content
		$string_for_barcode = 'Holding No: '.$this->data['main_data']->holding_number.', Name: '.$this->data['main_data']->family_person_name_head.', View Details: '.base_url().'/pub_view/'.$this->data['main_data']->uniqID;
		// barcode Content
	
		$barcodeobj = new TCPDF2DBarcode($string_for_barcode, 'QRCODE,H');

		// output the barcode as PNG image
		$img = $barcodeobj->getBarcodePNG(6, 6, array(0,0,0));

		// var_dump($img);die();
		$this->data['qr_code'] = $img;
		// Genarate Barcode 

		
		// $this->load->view('download/single_plate_view', $this->data);
		// die();

		// This is View File 
		$html = $this->load->view('download/single_plate_view', $this->data, true);

		// Genarate mpdf file 
		$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
		$fontDirs = $defaultConfig['fontDir'];

		$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
		$fontData = $defaultFontConfig['fontdata'];
		
		$mpdf = new \Mpdf\Mpdf([
			'format'=>'A4',
			'orientation'=>'L',
			'languageToFont' => new CustomLanguageToFontImplementation(),
			'autoScriptToLang' => true,
			'autoLangToFont' => true,
			'fontDir' => array_merge($fontDirs, [
				FCPATH . 'vendor/mpdf/mpdf/ttfonts',
				FCPATH . 'inc/assets/vendor/fonts'
			]),
			'margin_left' => 2,
			'margin_right' => 2,
			'margin_top' => 2,
			'margin_bottom' => 2,
			'margin_header' => 2,
			'margin_footer' => 2,
			'fontdata' => $fontData + [
				'solaimanlipi' => [
					'R' => 'SolaimanLipi.ttf',
					'useOTL' => 0xFF,
				],
				'kalpurush' => [
					'R' => 'kalpurush.ttf',
					'useOTL' => 0xFF,
				],
				'bangla' => [
					'R' => 'SolaimanLipi.ttf',
					'useOTL' => 0xFF,
				],
				'adorsholipi' => [
					'R' => 'AdorshoLipi.ttf',
					'useOTL' => 0xFF,
				],
				'freeserif' => [
					'R' => 'SolaimanLipi.ttf',
					'useOTL' => 0xFF,
				],
			],
			'default_font' => 'solaimanlipi',
			'mode' => 'utf-8'
		]);
		
		$fileName = $this->data['main_data']->family_person_name_head.' - '.$this->data['main_data']->holding_number.'_plate.pdf';
		$mpdf->defaultheaderline = 0;
		$mpdf->defaultfooterline = 0;
		// $mpdf->SetHeader('Document Title|Center Text|{PAGENO}');
		// $mpdf->SetFooter('Document Title');
		
		// Maximum Text exicute 
		ini_set("pcre.backtrack_limit", "5000000000");
		
		$stylesheet = file_get_contents(FCPATH.'inc/mpdf_inc/mpdfStyle.css'); // external css
		$mpdf->WriteHTML($stylesheet,1);
		$mpdf->WriteHTML($html,2);

		// $imsggggg = $mpdf->Output('', 'S');
		$mpdf->Output($fileName, 'D');
		// Genarate mpdf file 
	}


	public function s_plate_by_ward()
	{

		$ward_no = $this->input->get('ward');

		$this->data['main_datas'] = $this->FamilyModel->get_all_dataSheet_by_ward($ward_no); 

		$this->data['member_s'] = $this->FamilyModel->serach_up_member($ward_no); 


		// This is View File 
		$html = $this->load->view('download/plate_view_by_ward_no', $this->data, true);

		// Genarate mpdf file 
		$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
		$fontDirs = $defaultConfig['fontDir'];

		$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
		$fontData = $defaultFontConfig['fontdata'];

		$mpdf = new \Mpdf\Mpdf([
			'format'=>'A4',
			'orientation'=>'L',
			'languageToFont' => new CustomLanguageToFontImplementation(),
			'autoScriptToLang' => true,
			'autoLangToFont' => true,
			'fontDir' => array_merge($fontDirs, [
				FCPATH . 'vendor/mpdf/mpdf/ttfonts',
				FCPATH . 'inc/assets/vendor/fonts'
			]),
			'margin_left' => 2,
			'margin_right' => 2,
			'margin_top' => 2,
			'margin_bottom' => 2,
			'margin_header' => 2,
			'margin_footer' => 2,
			'fontdata' => $fontData + [
				'solaimanlipi' => [
					'R' => 'SolaimanLipi.ttf',
					'useOTL' => 0xFF,
				],
				'kalpurush' => [
					'R' => 'kalpurush.ttf',
					'useOTL' => 0xFF,
				],
				'bangla' => [
					'R' => 'SolaimanLipi.ttf',
					'useOTL' => 0xFF,
				],
				'adorsholipi' => [
					'R' => 'AdorshoLipi.ttf',
					'useOTL' => 0xFF,
				],
				'freeserif' => [
					'R' => 'SolaimanLipi.ttf',
					'useOTL' => 0xFF,
				],
			],
			'default_font' => 'solaimanlipi',
			'mode' => 'utf-8'
		]);
		
		$fileName = $ward_no.'-Ward_all_plate.pdf';
		$mpdf->defaultheaderline = 0;
		$mpdf->defaultfooterline = 0;
		$mpdf->packTableData = true;
		// $mpdf->SetHeader('Document Title|Center Text|{PAGENO}');
		// $mpdf->SetFooter('Document Title');

		// Maximum Text exicute 
		// ini_set("memory_limit","-1");
		ini_set("pcre.backtrack_limit", "5000000000");
		ini_set("memory_limit","512M"); 
		ini_set('max_execution_time', 300);
		
		$stylesheet = file_get_contents(FCPATH.'inc/mpdf_inc/mpdfStyle.css'); // external css
		$mpdf->WriteHTML($stylesheet,1);
		$mpdf->WriteHTML($html,2);

		// $mpdf->Output();
		$mpdf->Output($fileName,'D');
		// Genarate mpdf file 
	}

	// Download Sonod With Pad
	public function sonod_with_pad()
	{
		$uniq_id = $this->input->get('id');

		$this->data['get_data'] = $this->sonodM->getSonodByUniqID($uniq_id);
		if (empty($this->data['get_data'])) {
			show_404();
			return;
		}

		$tenant_id = (int)($this->data['get_data']->insididid_pridididid ?? tenant_code());
		$this->data['theme_config'] = $this->SettingModel->getTenantSonodThemeConfig($tenant_id);
		if (empty($this->data['ins_info']) || (int)($this->data['ins_info']->ins_titute_p_idds ?? 0) !== $tenant_id) {
			$this->data['ins_info'] = $this->SettingModel->getThisInsName($tenant_id);
		}

		$is_pad_mode = ($this->input->get('pad') == '1');
		$this->data['is_pad_mode'] = $is_pad_mode;

		// barcode Content
		$string_for_barcode = base_url().'sonod_verify/'.$this->data['get_data']->cgp_pr_idiidid_pr;

		$barcodeobj = new TCPDF2DBarcode($string_for_barcode, 'QRCODE,H');

		// output the barcode as PNG image (base64 string)
		$this->data['qr_code'] =  $barcodeobj->getBarcodePNG(4, 4, array(0,0,0));

		if ($this->data['get_data']->deft_cer_aididid == 2) {
			$this->data['dead_per'] = $this->sonodM->get_dead_person($uniq_id); 
			$this->data['warish_gons'] = $this->sonodM->get_all_warish_gon_person($uniq_id); 
		} elseif ($this->data['get_data']->deft_cer_aididid == 3 || $this->data['get_data']->deft_cer_aididid == 1230) {
			$this->data['trade_data'] = $this->sonodM->get_trade_license_data($uniq_id);
		}

		// This is View File 
		$html = $this->load->view('download/sonod_with_pad_view_file', $this->data, true);

		// Generate mpdf file 
		$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
		$fontDirs = $defaultConfig['fontDir'];

		$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
		$fontData = $defaultFontConfig['fontdata'];
		
		$mpdf = new \Mpdf\Mpdf([
			'format' => 'A4',
			'orientation' => 'P',
			'languageToFont' => new CustomLanguageToFontImplementation(),
			'autoScriptToLang' => true,
			'autoLangToFont' => true,
			'fontDir' => array_merge($fontDirs, [
				FCPATH . 'vendor/mpdf/mpdf/ttfonts',
				FCPATH . 'inc/assets/vendor/fonts'
			]),
			'margin_left' => 12,
			'margin_right' => 12,
			'margin_top' => 12,
			'margin_bottom' => 12,
			'fontdata' => $fontData + [
				'solaimanlipi' => [
					'R' => 'SolaimanLipi.ttf',
					'useOTL' => 0xFF,
				],
				'kalpurush' => [
					'R' => 'kalpurush.ttf',
					'useOTL' => 0xFF,
				],
				'bangla' => [
					'R' => 'SolaimanLipi.ttf',
					'useOTL' => 0xFF,
				],
				'adorsholipi' => [
					'R' => 'AdorshoLipi.ttf',
					'useOTL' => 0xFF,
				],
				'freeserif' => [
					'R' => 'SolaimanLipi.ttf',
					'useOTL' => 0xFF,
				],
				'arial' => [
					'R' => 'Arial.ttf',
					'useOTL' => 0xFF,
				],
			],
			'default_font' => 'solaimanlipi',
			'mode' => 'utf-8'
		]);
		
		$fileName = 'Sonod_' . ($this->data['get_data']->cgp_pr_idiidid_pr ?? $uniq_id) . '.pdf';
		$mpdf->defaultheaderline = 0;
		$mpdf->defaultfooterline = 0;
		
		ini_set("pcre.backtrack_limit", "5000000000");

		if (!$is_pad_mode) {
			$watermark_path = FCPATH . 'inc/assets/sonod_img/bd_gov.png';
			if (file_exists($watermark_path)) {
				$watermark_opacity = !empty($this->data['theme_config']['watermark_opacity']) ? floatval($this->data['theme_config']['watermark_opacity']) : 0.07;
				$mpdf->SetWatermarkImage($watermark_path, $watermark_opacity, [70, 70]);
				$mpdf->showWatermarkImage = true;
			}
		}
		
		$stylesheet = file_get_contents(FCPATH.'inc/mpdf_inc/sonodPStyle.css'); // external css
		if (!empty(trim($stylesheet))) {
			$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
		}
		$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::DEFAULT_MODE);

		$is_download = $this->input->get('download');
		if ($is_download) {
			$mpdf->Output($fileName, 'D');
		} else {
			$mpdf->Output($fileName, 'I');
		}
	}

	public function sonod_without_pad()
	{
		$uniq_id = $this->input->get('id');
		$this->data['main_data'] = $this->sonodM->getSonodByUniqID($uniq_id); 
		if (empty($this->data['main_data'])) {
			show_404();
			return;
		}

		if ($this->input->get('download') == '1') {
			$pad_param = ($this->input->get('pad') == '1') ? '&pad=1' : '';
			redirect('psonod?id=' . urlencode($uniq_id) . '&download=1' . $pad_param);
			return;
		}

		$tenant_id = (int)($this->data['main_data']->insididid_pridididid ?? tenant_code());
		$this->data['theme_config'] = $this->SettingModel->getTenantSonodThemeConfig($tenant_id);
		if (empty($this->data['ins_info']) || (int)($this->data['ins_info']->ins_titute_p_idds ?? 0) !== $tenant_id) {
			$this->data['ins_info'] = $this->SettingModel->getThisInsName($tenant_id);
		}

		// barcode Content
		$string_for_barcode = base_url().'sonod_verify/'.$this->data['main_data']->cgp_pr_idiidid_pr;
		$string_for_qrcode = base_url().'sonod_verify/'.$this->data['main_data']->cgp_pr_idiidid_pr;
		// barcode Content	
		$barcodeobj = new TCPDFBarcode($string_for_barcode, 'C128');
		$qrcodeobj = new TCPDF2DBarcode($string_for_barcode, 'QRCODE,H');

		// output the barcode as PNG image
		$this->data['bar_code'] = $barcodeobj->getBarcodePNG(3, 50, array(0,0,0));
		$this->data['qr_code'] = $qrcodeobj->getBarcodePNG(6, 6, array(0,0,0));
		// Genarate Barcode 

		if ($this->data['main_data']->deft_cer_aididid == 2) {
			$this->data['dead_per'] = $this->sonodM->get_dead_person($uniq_id); 
			$this->data['warish_gons'] = $this->sonodM->get_all_warish_gon_person($uniq_id); 
			$this->load->view('download/warish_sonod_view_file', $this->data);
		} elseif ($this->data['main_data']->deft_cer_aididid == 3 || $this->data['main_data']->deft_cer_aididid == 1230) {
			$this->data['trade_data'] = $this->sonodM->get_trade_license_data($uniq_id);
			$this->load->view('download/trade_license_view_file', $this->data);
		} else {
			$this->load->view('download/nagorik_sonod_view_file', $this->data);
		}

		
		// die();

		// This is View File 
		// $html = $this->load->view('download/single_plate_view', $this->data, true);

		/*
		// Genarate mpdf file 
		$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
		$fontDirs = $defaultConfig['fontDir'];

		$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
		$fontData = $defaultFontConfig['fontdata'];
		
		$mpdf = new \Mpdf\Mpdf([
			'format'=>'A4',
			'orientation'=>'L',
			'languageToFont' => new CustomLanguageToFontImplementation(),
			'fontDir' => array_merge($fontDirs, ['/fonts']),
			'margin_left' => 2,
			'margin_right' => 2,
			'margin_top' => 2,
			'margin_bottom' => 2,
			'margin_header' => 2,
			'margin_footer' => 2,
			'fontdata' => $fontData + [
				'solaimanlipi' => [
					'R' => 'SolaimanLipi.ttf',
					'useOTL' => 0xFF,
				],
				'bangla' => [
					'R' => 'Bangla.ttf',
					'useOTL' => 0xFF,
				],
				'arial' => [
					'R' => 'Arial.ttf',
					'useOTL' => 0xFF,
				],
				'adorshoLipi' => [
					'R' => 'AdorshoLipi.ttf',
				],
				'Nirmala' => [
					'R' => 'Nirmala.ttf',
					'useOTL' => 0xFF,
				],
			],
			'default_font' => 'solaimanlipi',
			'mode' => 'utf-8'
		]);
		
		$fileName = $this->data['main_data']->family_person_name_head.' - '.$this->data['main_data']->holding_number.'_plate.pdf';
		$mpdf->defaultheaderline = 0;
		$mpdf->defaultfooterline = 0;
		// $mpdf->SetHeader('Document Title|Center Text|{PAGENO}');
		// $mpdf->SetFooter('Document Title');
		
		// Maximum Text exicute 
		ini_set("pcre.backtrack_limit", "5000000000");
		
		$stylesheet = file_get_contents(FCPATH.'inc/mpdf_inc/mpdfStyle.css'); // external css
		$mpdf->WriteHTML($stylesheet,1);
		$mpdf->WriteHTML($html,2);

		// $imsggggg = $mpdf->Output('', 'S');
		$mpdf->Output($fileName, 'D');
		// Genarate mpdf file 
		*/
	}










    public function print_recite_forms()
    {
		$unq_id = $this->input->get('uniqs');

        $this->data['paid_amnt_'] = $this->FamilyModel->get_paidAmntByVocherNo($unq_id); 
        $this->data['invoices'] = $this->FamilyModel->get_invoiceByVocherNo($unq_id); 

		$this->load->view('download/print_recite_view_files', $this->data);
    }
	
    public function get_all_uniqs_data_by_word()
    {
		$ward = $this->input->get('word_no');		
        $this_data_paid_amnt_ = $this->FamilyModel->getAllUniqsDataByWord($ward); 
		$this->output->set_content_type('application/json')->set_output(json_encode($this_data_paid_amnt_)); 		
    }

	public function test()
	{
		$imagick = new Imagick();

		$imagick->readImage('1.pdf');

		$imagick->writeImages('converted.jpg', false);
	}

	public function export_all_global_showcase()
	{
		$with_photo = ($this->input->get('photo') !== '0');
		$sub_dir = $with_photo ? 'With-Citizen-Photo' : 'Without-Photo';
		$output_dir = FCPATH . 'Global-Sonod-Showcase/' . $sub_dir;
		if (!is_dir($output_dir)) {
			mkdir($output_dir, 0755, true);
		}

		$tenant_id = 1;
		$ins_info = $this->SettingModel->getThisInsName($tenant_id);
		$theme_config = $this->SettingModel->getTenantSonodThemeConfig($tenant_id);

		$all_global = $this->db->order_by('deft_cer_all_au_iddd', 'asc')->get('deft_cer_all')->result();

		$defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
		$fontDirs = $defaultConfig['fontDir'];
		$defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
		$fontData = $defaultFontConfig['fontdata'];

		$mpdf_config = [
			'format' => 'A4',
			'orientation' => 'P',
			'languageToFont' => new CustomLanguageToFontImplementation(),
			'autoScriptToLang' => true,
			'autoLangToFont' => true,
			'fontDir' => array_merge($fontDirs, [
				FCPATH . 'vendor/mpdf/mpdf/ttfonts',
				FCPATH . 'inc/assets/vendor/fonts'
			]),
			'margin_left' => 12,
			'margin_right' => 12,
			'margin_top' => 12,
			'margin_bottom' => 12,
			'fontdata' => $fontData + [
				'solaimanlipi' => ['R' => 'SolaimanLipi.ttf', 'useOTL' => 0xFF],
				'kalpurush'    => ['R' => 'kalpurush.ttf', 'useOTL' => 0xFF],
				'bangla'       => ['R' => 'SolaimanLipi.ttf', 'useOTL' => 0xFF],
				'adorsholipi'  => ['R' => 'AdorshoLipi.ttf', 'useOTL' => 0xFF],
				'freeserif'    => ['R' => 'SolaimanLipi.ttf', 'useOTL' => 0xFF],
				'arial'        => ['R' => 'Arial.ttf', 'useOTL' => 0xFF],
			],
			'default_font' => 'solaimanlipi',
			'mode' => 'utf-8'
		];

		$stylesheet = file_get_contents(FCPATH . 'inc/mpdf_inc/sonodPStyle.css');

		$results = [];
		$counter = 1;

		foreach ($all_global as $cer) {
			$gid = (int)$cer->deft_cer_all_au_iddd;
			$is_en = ((int)$cer->en_bn_type === 2);
			$sonod_title = !empty($cer->certify_title_text_s) ? $cer->certify_title_text_s : $cer->certify_titles;

			$sonod_num_str = sprintf("20261014%06d", $counter);

			$barcodeobj = new TCPDF2DBarcode(base_url() . 'sonod_verify/' . $sonod_num_str, 'QRCODE,H');
			$qr_code_base64 = $barcodeobj->getBarcodePNG(4, 4, array(0, 0, 0));

			$mock_get_data = (object)[
				'sonod_gen_ididid'            => $counter,
				'cgp_pr_idiidid_pr'           => $sonod_num_str,
				'sonod_uniqs'                 => 'showcase_' . $gid,
				'insididid_pridididid'        => $tenant_id,
				'deft_cer_aididid'            => $gid,
				'certificate_title_head_text' => $sonod_title,
				'certify_titles'              => $cer->certify_titles,
				'certify_detail_ss'           => $cer->certify_detail_ss,
				'sonod_details_html'          => $cer->certify_detail_ss,
				'en_bn_type'                  => $cer->en_bn_type,
				'timeing_stmpsss'             => strtotime('2026-10-14'),
				'dating_stampingsss'          => '14-10-2026',
				// Citizen mock details
				'applicant_photo'             => ($with_photo && $gid != 2) ? 'tenant-1/citizen-6ace5bb44733e.png' : '',
				'ver_per_info_name_bn'        => $is_en ? 'Md. Rafiqul Islam' : 'সাকা চৌধুরী',
				'ver_per_info_father_name_bn' => $is_en ? 'Late Abdul Karim' : 'মরহুম হাজী আব্দুল করিম',
				'ver_per_info_mother_name_bn' => $is_en ? 'Mst. Rahima Khatun' : 'মোসাম্মৎ রহিমা খাতুন',
				'nid_no17'                    => $is_en ? '19851515322010123' : '১৯৮৫১৫১৫৩২২০১০১২৩',
				'ver_per_info_ward_no'        => $is_en ? '04' : '৪',
				'custom_village_name'         => $is_en ? 'Middle Dhum' : 'ধুম মধ্যম পাড়া',
				'custom_house_name'           => $is_en ? '12/A' : '১২/এ',
				'mobile_number'               => $is_en ? '01813152799' : '০১৮১৩১৫২৭৯৯',
			];

			$view_data = [
				'get_data'      => $mock_get_data,
				'ins_info'      => $ins_info,
				'theme_config'  => $theme_config,
				'is_pad_mode'   => false,
				'qr_code'       => $qr_code_base64
			];

			// Handle Warish Data
			if ($gid == 2) {
				$view_data['dead_per'] = (object)[
					'd_name'    => 'মরহুম আব্দুল গফুর',
					'd_f_name'  => 'মরহুম মৌলভী মকবুল আলী',
					'd_m_name'  => 'মরহুমা আছিয়া খাতুন',
					'd_village' => 'ধুম মধ্যম পাড়া',
					'd_ward'    => '৪'
				];
				$view_data['warish_gons'] = [
					(object)['w_name' => 'মোসাম্মৎ সুফিয়া বেগম', 'w_relation' => 'স্ত্রী', 'w_age' => '৪৫', 'w_nid' => '১৯৭৯১৫১৫৩২২০১০০১২'],
					(object)['w_name' => 'মোহাম্মদ তারেক হাসান', 'w_relation' => 'পুত্র', 'w_age' => '২৪', 'w_nid' => '২০০২১৫১৫৩২২০১০৩৪৫'],
					(object)['w_name' => 'ফারহানা ইয়াসমিন', 'w_relation' => 'কন্যা', 'w_age' => '১৯', 'w_nid' => '২০০৭১৫১৫৩২২০১০৭৮৯'],
					(object)['w_name' => 'তানভীর গফুর', 'w_relation' => 'পুত্র', 'w_age' => '১৬', 'w_nid' => '২০১০১৫১৫৩২২০১০৯০১'],
				];
			}

			// Handle Trade License Data
			if ($gid == 3 || $gid == 1230) {
				if ($gid == 3) {
					$mock_get_data->ver_per_info_name_bn        = 'মুহাম্মদ নাজমুল হক';
					$mock_get_data->ver_per_info_father_name_bn = 'মুহাম্মদ রসুল আহমদ';
					$mock_get_data->ver_per_info_mother_name_bn = 'আয়েশা খাতুন';
					$mock_get_data->nid_no17                    = '৮৬৯১৩৬১৮৩৯';
					$mock_get_data->mobile_number               = '০১৭৩০১৯৭৩৫৮';
					$mock_get_data->ver_per_info_ward_no        = '০৭';
					$mock_get_data->custom_village_name         = 'ধুম';
					$mock_get_data->applicant_photo             = 'tenant-1/citizen-trade-sample.png';
				} elseif ($gid == 1230) {
					$mock_get_data->ver_per_info_name_bn        = 'Muhammad Nazmul Haque';
					$mock_get_data->ver_per_info_name_en        = 'Muhammad Nazmul Haque';
					$mock_get_data->ver_per_info_father_name_bn = 'Muhammad Rasul Ahmed';
					$mock_get_data->ver_per_info_father_name_en = 'Muhammad Rasul Ahmed';
					$mock_get_data->ver_per_info_mother_name_bn = 'Ayesha Khatun';
					$mock_get_data->ver_per_info_mother_name_en = 'Ayesha Khatun';
					$mock_get_data->nid_no17                    = '8691361839';
					$mock_get_data->mobile_number               = '01730197358';
					$mock_get_data->ver_per_info_ward_no        = '07';
					$mock_get_data->custom_village_name         = 'Dhum';
					$mock_get_data->applicant_photo             = 'tenant-1/citizen-trade-sample.png';
				}
				$view_data['trade_data'] = (object)[
					'organization_name'    => $is_en ? 'Nazmul Electric' : 'নাজমুল ইলেক্ট্রিক',
					'business_ins_name'    => $is_en ? 'Nazmul Electric' : 'নাজমুল ইলেক্ট্রিক',
					'business_type'        => $is_en ? 'Electric & Electronics Equipment Sales' : 'বৈদ্যুতিক মালামাল বিক্রয়',
					'business_types'       => $is_en ? 'Electric & Electronics Equipment Sales' : 'বৈদ্যুতিক মালামাল বিক্রয়',
					'fiscal_year'          => $is_en ? '2026-2027' : '২০২৬-২০২৭',
					'license_yearss'       => $is_en ? '2026-2027' : '২০২৬-২০২৭',
					'sonod_no_gens'        => $is_en ? '03000009' : '০৩০০০০০৯',
					'license_issue_date'   => $is_en ? '24-07-2025' : '২৪-০৭-২০২৫',
					'renewal_date'         => $is_en ? '30-09-2026' : '৩০-০৯-২০২৬',
					'license_expires_date' => $is_en ? '30-06-2027' : '৩০-০৬-২০২৭',
					'muldhon'              => $is_en ? '5,00,000' : '৫,০০,০০০',
					'license_fee_yearly'   => $is_en ? '200/-' : '২০০/-',
					'license_fee_vats'     => $is_en ? '30/-' : '৩০/-',
					'pesha_bebsa_o_kor'    => $is_en ? '770/-' : '৭৭০/-',
					'adds_texts'           => '',
					'bokeya'               => '',
					'services_charge'      => '',
					'total_charge'         => $is_en ? '1000/-' : '১০০০/-',
				];
			}

			$html = $this->load->view('download/sonod_with_pad_view_file', $view_data, true);

			$mpdf = new \Mpdf\Mpdf($mpdf_config);
			$mpdf->defaultheaderline = 0;
			$mpdf->defaultfooterline = 0;

			$watermark_path = FCPATH . 'inc/assets/sonod_img/bd_gov.png';
			if (file_exists($watermark_path)) {
				$mpdf->SetWatermarkImage($watermark_path, 0.06, [70, 70]);
				$mpdf->showWatermarkImage = true;
			}

			if (!empty(trim($stylesheet))) {
				$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
			}
			$mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::DEFAULT_MODE);

			$name_map = [
				1    => '01_Nagorik_Sonod (নাগরিকত্ব সনদপত্র).pdf',
				2    => '02_Warish_Sonod (ওয়ারিশ সনদপত্র).pdf',
				3    => '03_Trade_License (ট্রেড লাইসেন্স সনদ).pdf',
				5    => '04_Prottayon_Patra (প্রত্যয়নপত্র).pdf',
				6    => '05_Ekoi_Namer_Prottayon (একই নামের প্রত্যয়ন).pdf',
				7    => '06_Charitrik_Sonod (চারিত্রিক সনদপত্র).pdf',
				8    => '07_Obibahito_Sonod (অবিবাহিত সনদপত্র).pdf',
				9    => '08_Paribarik_Sonod (পারিবারিক সনদপত্র).pdf',
				10   => '09_Punobibaho_Na_Howa_Sonod (পুনঃ বিবাহ না হওয়া সনদপত্র).pdf',
				11   => '10_Dwaito_Nam_Prokash_Sonod (দ্বৈত নাম প্রকাশের সনদ).pdf',
				12   => '11_Obhibhaboker_Ayer_Sonod (অভিভাবকের আয়ের সনদপত্র).pdf',
				30   => '12_NID_Songshodhon_Prottayon (জাতীয় পরিচয়পত্র সংশোধন প্রত্যয়ন).pdf',
				1221 => '13_Mrittu_Sonod (মৃত্যু সনদ).pdf',
				1222 => '14_Citizenship_Certificate.pdf',
				1223 => '15_Character_Certificate.pdf',
				1224 => '16_Unmarried_Certificate.pdf',
				1225 => '17_Family_Certificate.pdf',
				1226 => '18_Income_Certificate.pdf',
				1227 => '19_Testimonial.pdf',
				1228 => '20_Same_Person_Certificate.pdf',
				1229 => '21_Non_Remarriage_Certificate.pdf',
				1230 => '22_Trade_License_Certificate.pdf',
				1231 => '23_NID_Correction_Recommendation.pdf',
				1232 => '24_Death_Certificate.pdf',
			];

			$file_name = $name_map[$gid] ?? sprintf("%02d_Sonod_%d.pdf", $counter, $gid);
			$full_file_path = $output_dir . '/' . $file_name;

			$mpdf->Output($full_file_path, 'F');
			if ($gid == 3) {
				copy($full_file_path, FCPATH . 'Trade_License_Sample.pdf');
			}

			$results[] = [
				'id'        => $gid,
				'title'     => $sonod_title,
				'file'      => $file_name,
				'size_kb'   => round(filesize($full_file_path) / 1024, 2)
			];

			$counter++;
		}

		$this->output->set_content_type('application/json')->set_output(json_encode([
			'status'     => 'success',
			'message'    => 'সকল গ্লোবাল সনদের কপি সফলভাবে তৈরি করা হয়েছে।',
			'total'      => count($results),
			'output_dir' => $output_dir,
			'files'      => $results
		], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	}

}