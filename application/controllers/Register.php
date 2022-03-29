<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property User_model $user_model
 */
class Register extends CI_Controller
{
	public function index()
	{
		$this->load->model('user_model');
		$this->load->helper('file');

		$valid_fields = array('nik', 'user_id', 'password', 'ip', 'image');

		if ($this->input->method() == "post")
		{
			//Ambil input dari $_POST
			$post = json_decode($this->input->raw_input_stream, true);

			//Memeriksa seluruh input harus memiliki key yang cocok dengan valid_fields
			//Jika ada input key yang tidak cocok, return Error Parameter Tidak Sesuai
			if (!is_valid_fields($post, $valid_fields)) {
				json_response_error(
					6019,
					"Parameter Tidak Sesuai",
					null
				);
			}

			//Memeriksa fields yang harus/wajib diisi,
			//jika ada yang kosong, return Error Parameter Tidak Sesuai
			$required_fields = array('nik', 'user_id', 'password', 'image');
			if (!required_has_values($post, $required_fields))
			{
				json_response_error(
					6019,
					"Parameter Tidak Sesuai",
					null
				);
			}

			//Jika IP address tidak dicantumkan, maka inject dengan deteksi otomatis
			if (empty($post['ip'])) $post['ip'] = $this->input->ip_address();

			//IMAGE --------------------------------------------------------------------
			$file = file_get_contents($post['image']);
			$realpath = "public" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR;
			$filename = $realpath . $post['nik'] . ".jpg";
			try {
				//ubah base64 menjadi file, lalu simpan ke temporary folder
				$tmppath = "public" . DIRECTORY_SEPARATOR . "tmp" . DIRECTORY_SEPARATOR;
				$tmpfile = $tmppath . $post['nik'] . ".tmp";
				file_put_contents($tmpfile, $file);

				//check mime type nya, harus jpg/jpeg
				$mimetype = mime_content_type($tmpfile);
				$valid_mime_types = array('image/jpg', 'image/jpeg');

				//jika bukan jpg/jpeg, maka
				if (!in_array($mimetype, $valid_mime_types))
				{
					//hapus file dari folder temporary
					unlink($tmpfile);

					//lalu kirimkan response error
					json_response_error(6015, "Foto tidak dapat diproses");
				}

				//jika mime type jpg/jpeg, maka
				//periksa apakah file temporary masih ada
				//jika ada, hapus temporary file nya
				if (file_exists($tmpfile)) unlink($tmpfile);

				//lalu copy file yang real (jpg/jpeg) ke lokasi yang benar (ke folder images)
				file_put_contents($filename, $file);

			} catch (Exception $e) {
				//jika terjadi error saat pembuatan file, maka
				//tampilkan error response
				json_response_error(6015, "Foto tidak dapat diproses");
			}

			//Ambil data user dari database, dgn parameter user_id atau nik
			if ($user = $this->user_model->is_user_exists($post['nik'], $post['user_id']))
			{
				json_response_error(5000, "Akun telah terdaftar");
			}

			$post['password'] = md5($post['password']);
			unset($post['image']);

			if ($new_user = $this->user_model->insert_user($post))
			{
				json_response_success($new_user);
			}

			json_response_error(500, "Sistem Sedang Sibuk");

		} else {
			$this->load->view('register');
		}
	}
}
