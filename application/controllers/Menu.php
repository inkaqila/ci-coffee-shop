<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Menu extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session'); // ✅ Load session
        $this->load->model('Menu_model'); // ✅ Load model jika digunakan

        // Hitung notifikasi pesanan belum lunas
        $pesanan = $this->db->get_where('pesanan', ['lunas' => 0])->result_array();
        $this->data['notif_pesanan'] = 0;

        if (!empty($pesanan)) {
            foreach ($pesanan as $p) {
                $this->data['notif_pesanan'] += $p['quantity'];
            }
        }
    }

    public function index()
    {
        $data = $this->data;
        $data['title'] = 'Menu';

        // Hitung total menu
        $totalMenu = $this->db->get('menu')->num_rows();

        // Konfigurasi pagination
        $this->load->library('pagination');
        $config['base_url'] = site_url('menu/index');
        $config['total_rows'] = $totalMenu;
        $config['per_page'] = 4;

        // Styling pagination
        $config['full_tag_open'] = '<nav><ul class="pagination">';
        $config['full_tag_close'] = '</ul></nav>';
        $config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close'] = '</a></li>';
        $config['num_tag_open'] = '<li class="page-item">';
        $config['num_tag_close'] = '</li>';
        $config['next_link'] = '&raquo;';
        $config['next_tag_open'] = '<li class="page-item">';
        $config['next_tag_close'] = '</li>';
        $config['prev_link'] = '&laquo;';
        $config['prev_tag_open'] = '<li class="page-item">';
        $config['prev_tag_close'] = '</li>';
        $config['attributes'] = array('class' => 'page-link');

        $this->pagination->initialize($config);

        // Ambil data menu dengan pagination
        $start = $this->uri->segment(3) ? $this->uri->segment(3) : 0;
        $data['menu'] = $this->Menu_model->getAllMenu($config['per_page'], $start);

        // Load view
        $this->load->view('layouts/_header', $data);
        $this->load->view('menu/index', $data);
        $this->load->view('layouts/_footer');
    }

    public function pesan($menu_id)
    {
        // ✅ Validasi menu ID
        $menu = $this->db->get_where('menu', ['id' => $menu_id])->row_array();
        if (!$menu) {
            show_error('Menu tidak ditemukan!', 404);
        }

        $harga = $menu['harga'];

        // ✅ Buat nomor pesanan secara otomatis
        $this->db->order_by('created_at', 'DESC');
        $this->db->limit(1);
        $last_order = $this->db->get('pesanan')->row_array();
        
        $today = date('Ymd'); // Format: 20240203
        if ($last_order) {
            $last_no = intval(substr($last_order['no_pesanan'], -3)); // Ambil angka terakhir
            $next_no = str_pad($last_no + 1, 3, '0', STR_PAD_LEFT); // Formatkan jadi 001, 002, dst
        } else {
            $next_no = '001';
        }
        
        $no_pesanan = "ORD" . $today . $next_no; // Contoh: ORD20240203001

        // ✅ Periksa apakah menu sudah ada di pesanan
        $pesananSudahAda = $this->db->get_where('pesanan', ['menu_id' => $menu_id, 'lunas' => 0])->row_array();

        if ($pesananSudahAda) {
            // ✅ Update pesanan jika menu sudah ada
            $data = [
                'quantity' => $pesananSudahAda['quantity'] + 1,
                'subtotal' => $pesananSudahAda['subtotal'] + $harga
            ];

            $this->db->where('menu_id', $menu_id);
            $this->db->where('lunas', 0);
            $this->db->update('pesanan', $data);
        } else {
            // ✅ Tambahkan menu baru ke pesanan
            $data = [
                'no_pesanan' => $no_pesanan,
                'menu_id'    => $menu_id,
                'quantity'   => 1,
                'subtotal'   => $harga,
                'lunas'      => 0
            ];

            $this->db->insert('pesanan', $data);
        }

        // ✅ Redirect ke halaman menu dengan pesan sukses
        $this->session->set_flashdata('success', 'Menu berhasil ditambahkan ke pesanan!');
        redirect('menu');
    }
}
