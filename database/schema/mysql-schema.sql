/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL DEFAULT 'administrator',
  `password` varchar(100) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `no_telp` varchar(30) NOT NULL,
  `akses_level` enum('pemilik','petugas') NOT NULL DEFAULT 'petugas',
  `unit` int(11) NOT NULL DEFAULT 0,
  `blokir` enum('Y','N') NOT NULL DEFAULT 'N',
  `mpengguna` varchar(1) NOT NULL DEFAULT 'N',
  `mheader` varchar(1) NOT NULL DEFAULT 'N',
  `mjenisbayar` varchar(1) NOT NULL DEFAULT 'N',
  `mpelanggan` varchar(1) NOT NULL DEFAULT 'N',
  `msupplier` varchar(1) NOT NULL DEFAULT 'N',
  `msatuan` varchar(1) NOT NULL DEFAULT 'N',
  `mjenisobat` varchar(1) NOT NULL DEFAULT 'N',
  `mbarang` varchar(1) NOT NULL DEFAULT 'N',
  `tbm` varchar(1) NOT NULL DEFAULT 'N',
  `tbmpbf` varchar(1) NOT NULL DEFAULT 'N',
  `tpk` varchar(1) NOT NULL DEFAULT 'N',
  `lpitem` varchar(1) NOT NULL DEFAULT 'N',
  `lpbrgmasuk` varchar(1) NOT NULL DEFAULT 'N',
  `lpkasir` varchar(1) NOT NULL DEFAULT 'N',
  `lpsupplier` varchar(1) NOT NULL DEFAULT 'N',
  `lppelanggan` varchar(1) NOT NULL DEFAULT 'N',
  `mstok` varchar(1) NOT NULL DEFAULT 'N',
  `stok_kritis` varchar(1) NOT NULL DEFAULT 'N',
  `orders` varchar(1) NOT NULL DEFAULT 'N',
  `penjualansebelum` varchar(1) NOT NULL DEFAULT 'N',
  `labapenjualan` varchar(1) NOT NULL DEFAULT 'N',
  `byrkredit` varchar(1) NOT NULL DEFAULT 'N',
  `stokopname` varchar(1) NOT NULL DEFAULT 'N',
  `soharian` varchar(1) NOT NULL DEFAULT 'N',
  `labajenisobat` varchar(1) NOT NULL DEFAULT 'N',
  `koreksistok` varchar(1) NOT NULL DEFAULT 'N',
  `shiftkerja` varchar(1) NOT NULL DEFAULT 'N',
  `neraca` varchar(1) NOT NULL DEFAULT 'N',
  `lapstokopname` varchar(1) NOT NULL DEFAULT 'N',
  `komisi` varchar(1) NOT NULL DEFAULT 'N',
  `kartustok` varchar(1) NOT NULL DEFAULT 'N',
  `catatan` varchar(1) NOT NULL DEFAULT 'N',
  `cekdarah` varchar(1) NOT NULL DEFAULT 'N',
  `jurnalkas` varchar(1) NOT NULL DEFAULT 'N',
  `ujian` varchar(1) NOT NULL DEFAULT 'N',
  `foto` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `articles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `slug` varchar(255) NOT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `banners`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banners` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `judul` varchar(255) NOT NULL,
  `deskripsi` text NOT NULL,
  `status` enum('active','inactive') NOT NULL,
  `foto` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `barang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `barang` (
  `id_barang` int(11) NOT NULL AUTO_INCREMENT,
  `kd_barang` bigint(20) NOT NULL,
  `category_id` int(11) NOT NULL,
  `nm_barang` varchar(100) NOT NULL,
  `stok_barang` double NOT NULL,
  `stok_buffer` double NOT NULL,
  `stok_grosir` int(11) NOT NULL,
  `sat_barang` varchar(15) NOT NULL,
  `sat_grosir` varchar(15) NOT NULL,
  `konversi` int(11) NOT NULL,
  `jenisobat` varchar(50) NOT NULL,
  `hna` double NOT NULL,
  `diskon` double NOT NULL,
  `hrgsat_barang` double NOT NULL,
  `hrgsat_grosir` int(11) NOT NULL,
  `hrgjual_barang` double NOT NULL,
  `hrgjual_barang1` double NOT NULL,
  `hrgjual_barang2` double NOT NULL,
  `komisi` double NOT NULL,
  `zataktif` varchar(250) NOT NULL,
  `indikasi` text NOT NULL,
  `ket_barang` text NOT NULL,
  `dosis` varchar(100) NOT NULL,
  `waktu` datetime NOT NULL,
  `t30` int(11) NOT NULL,
  `t60` int(11) DEFAULT 0,
  `gr` int(11) DEFAULT 0,
  `q30` int(11) NOT NULL,
  `petugas` varchar(100) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'inactive',
  `image` varchar(100) DEFAULT NULL,
  `promosi` enum('standar','terlaris','diskon') NOT NULL DEFAULT 'standar',
  `tgl` date DEFAULT NULL,
  `updated_by` varchar(30) NOT NULL,
  PRIMARY KEY (`id_barang`),
  KEY `idx_barang_kd_barang` (`kd_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `barang_supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `barang_supplier` (
  `id_brgsup` int(11) NOT NULL AUTO_INCREMENT,
  `id_supplier` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `hrgsat_brgsupplier` double NOT NULL,
  PRIMARY KEY (`id_brgsup`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `batch`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `batch` (
  `id_batch` int(11) NOT NULL AUTO_INCREMENT,
  `tgl_transaksi` datetime NOT NULL,
  `no_batch` varchar(10) NOT NULL,
  `exp_date` date NOT NULL,
  `qty` double NOT NULL,
  `satuan` varchar(20) NOT NULL,
  `kd_transaksi` varchar(30) NOT NULL,
  `kd_barang` varchar(50) NOT NULL,
  `status` enum('masuk','keluar') NOT NULL,
  PRIMARY KEY (`id_batch`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bundle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bundle` (
  `id_bundle` int(11) NOT NULL AUTO_INCREMENT,
  `kd_bundle` varchar(50) NOT NULL,
  `nm_bundle` varchar(100) NOT NULL,
  `sat_bundle` varchar(50) NOT NULL,
  `qty_bundle` int(11) NOT NULL,
  `hrgjual_bundle` double NOT NULL,
  `petugas` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `update_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id_bundle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `bundle_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bundle_detail` (
  `idbundle_detail` int(11) NOT NULL AUTO_INCREMENT,
  `kd_bundle` varchar(50) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `kd_barang` bigint(20) NOT NULL,
  `nm_barang` varchar(100) NOT NULL,
  `qty_barang` int(11) NOT NULL,
  `sat_barang` varchar(15) NOT NULL,
  `hrgjual_barang` double NOT NULL,
  `subtotal` double NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `update_at` datetime DEFAULT NULL,
  PRIMARY KEY (`idbundle_detail`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` bigint(20) NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `carabayar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `carabayar` (
  `id_carabayar` int(11) NOT NULL AUTO_INCREMENT,
  `nm_carabayar` varchar(100) NOT NULL,
  `urutan` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_carabayar`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `catatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `catatan` (
  `id_catatan` int(11) NOT NULL AUTO_INCREMENT,
  `tgl` date NOT NULL,
  `shift` int(11) NOT NULL,
  `petugas` varchar(30) NOT NULL,
  `deskripsi` text NOT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_catatan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_name_unique` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cekdarah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cekdarah` (
  `id_cekdarah` int(11) NOT NULL AUTO_INCREMENT,
  `gula` varchar(50) NOT NULL,
  `asamurat` varchar(50) NOT NULL,
  `kolesterol` varchar(50) NOT NULL,
  `tensi` varchar(50) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `petugas` varchar(50) NOT NULL,
  `waktu` datetime NOT NULL,
  PRIMARY KEY (`id_cekdarah`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `company_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `company_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_perusahaan` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `qris_image` varchar(255) DEFAULT NULL,
  `notification_sound` varchar(255) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `peta_lokasi` text DEFAULT NULL,
  `telepon` varchar(14) DEFAULT NULL,
  `catatan` varchar(256) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cpp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cpp` (
  `id_cpp` int(11) NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int(11) DEFAULT NULL,
  `no_cpp` varchar(20) DEFAULT NULL,
  `nama_pasien` varchar(255) DEFAULT NULL,
  `jk` varchar(20) DEFAULT NULL,
  `umur` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `telp` varchar(50) DEFAULT NULL,
  `tgl_ttd` varchar(100) DEFAULT NULL,
  `thn_ttd` varchar(10) DEFAULT NULL,
  `nama_apoteker` varchar(255) DEFAULT NULL,
  `sipa_apoteker` varchar(100) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_cpp`),
  KEY `id_pelanggan` (`id_pelanggan`),
  CONSTRAINT `fk_cpp_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cpp_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cpp_detail` (
  `id_detail` int(11) NOT NULL AUTO_INCREMENT,
  `id_cpp` int(11) NOT NULL,
  `no_urut` int(11) DEFAULT NULL,
  `tanggal` varchar(100) DEFAULT NULL,
  `nama_dokter` varchar(255) DEFAULT NULL,
  `nama_obat_dosis` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `id_cpp` (`id_cpp`),
  CONSTRAINT `fk_cpp_detail` FOREIGN KEY (`id_cpp`) REFERENCES `cpp` (`id_cpp`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `hasil_ujian`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `hasil_ujian` (
  `id_hasil` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `nama_lengkap` varchar(150) DEFAULT NULL,
  `ujian_id` int(11) DEFAULT NULL,
  `nama_ujian` varchar(100) DEFAULT NULL,
  `total_soal` int(11) NOT NULL DEFAULT 0,
  `jawaban_benar` int(11) NOT NULL DEFAULT 0,
  `jawaban_salah` int(11) NOT NULL DEFAULT 0,
  `tidak_dijawab` int(11) NOT NULL DEFAULT 0,
  `soal_tidak_valid` int(11) NOT NULL DEFAULT 0,
  `nilai_akhir` decimal(5,2) NOT NULL DEFAULT 0.00,
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_selesai` datetime NOT NULL,
  `durasi_detik` int(11) DEFAULT NULL,
  `durasi_batas_detik` int(11) DEFAULT NULL,
  `status_waktu` enum('on_time','timeout') NOT NULL DEFAULT 'on_time',
  `jawaban_json` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_hasil`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `homecare`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homecare` (
  `id_homecare` int(11) NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int(11) DEFAULT NULL,
  `no_homecare` varchar(20) DEFAULT NULL,
  `nama_pasien` varchar(255) DEFAULT NULL,
  `umur` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `telp` varchar(50) DEFAULT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_homecare`),
  KEY `id_pelanggan` (`id_pelanggan`),
  CONSTRAINT `fk_homecare_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `homecare_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `homecare_detail` (
  `id_detail` int(11) NOT NULL AUTO_INCREMENT,
  `id_homecare` int(11) NOT NULL,
  `no_urut` int(11) DEFAULT NULL,
  `tgl_kunjungan` varchar(100) DEFAULT NULL,
  `catatan_apoteker` text DEFAULT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `id_homecare` (`id_homecare`),
  CONSTRAINT `fk_homecare_detail` FOREIGN KEY (`id_homecare`) REFERENCES `homecare` (`id_homecare`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jenis_jurnal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jenis_jurnal` (
  `idjenis` int(11) NOT NULL AUTO_INCREMENT,
  `nm_jurnal` varchar(100) NOT NULL,
  `tipe` int(11) NOT NULL,
  PRIMARY KEY (`idjenis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jenis_obat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jenis_obat` (
  `idjenis` int(11) NOT NULL AUTO_INCREMENT,
  `jenisobat` varchar(50) NOT NULL,
  `ket` varchar(250) NOT NULL,
  PRIMARY KEY (`idjenis`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jenispenjualan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jenispenjualan` (
  `id_penjualan` int(11) NOT NULL AUTO_INCREMENT,
  `nm_penjualan` varchar(20) NOT NULL,
  PRIMARY KEY (`id_penjualan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_sinkronisasi_stok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `job_sinkronisasi_stok` (
  `id_job` int(11) NOT NULL AUTO_INCREMENT,
  `keterangan` varchar(100) NOT NULL,
  `waktu` datetime NOT NULL,
  PRIMARY KEY (`id_job`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jurnal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `jurnal` (
  `id_jurnal` int(11) NOT NULL AUTO_INCREMENT,
  `tanggal` date NOT NULL,
  `ket` text NOT NULL,
  `petugas` varchar(30) NOT NULL,
  `idjenis` mediumint(100) NOT NULL,
  `debit` int(11) NOT NULL,
  `kredit` int(11) NOT NULL,
  `carabayar` varchar(8) NOT NULL,
  `current` datetime NOT NULL,
  PRIMARY KEY (`id_jurnal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kartu_stok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kartu_stok` (
  `id_kartu` int(11) NOT NULL AUTO_INCREMENT,
  `kode_transaksi` varchar(100) NOT NULL,
  `tgl_sekarang` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_kartu`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kas` (
  `id_kas` int(11) NOT NULL,
  `saldo` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kdbm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kdbm` (
  `id_kdbm` int(11) NOT NULL AUTO_INCREMENT,
  `kd_trbmasuk` varchar(100) NOT NULL,
  `id_resto` varchar(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `stt_kdbm` varchar(3) NOT NULL DEFAULT 'ON',
  PRIMARY KEY (`id_kdbm`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kdtk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kdtk` (
  `id_kdtk` int(11) NOT NULL AUTO_INCREMENT,
  `kd_trkasir` varchar(100) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `stt_kdtk` varchar(3) NOT NULL DEFAULT 'ON',
  PRIMARY KEY (`id_kdtk`),
  UNIQUE KEY `kd_trkasir` (`kd_trkasir`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `komisi_pegawai`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `komisi_pegawai` (
  `id_komisi` int(11) NOT NULL AUTO_INCREMENT,
  `kd_trkasir` varchar(100) NOT NULL,
  `id_dtrkasir` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `ttl_komisi` double NOT NULL,
  `tgl_komisi` date NOT NULL,
  `status_komisi` enum('on','closed') NOT NULL,
  PRIMARY KEY (`id_komisi`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `komisiglobal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `komisiglobal` (
  `id_komisiglobal` int(11) NOT NULL AUTO_INCREMENT,
  `nilai` int(11) NOT NULL,
  `tgl` date NOT NULL,
  `petugas` varchar(50) NOT NULL,
  `status` varchar(3) NOT NULL,
  PRIMARY KEY (`id_komisiglobal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `konseling`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `konseling` (
  `id_konseling` int(11) NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int(11) NOT NULL,
  `nm_pelanggan` varchar(100) NOT NULL,
  `tgl_konseling` date NOT NULL,
  `id_admin` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `nama_dokter` varchar(100) NOT NULL,
  `diagnosa` text NOT NULL,
  `riwayat_penyakit` text NOT NULL,
  `riwayat_alergi` text NOT NULL,
  `keluhan` text NOT NULL,
  `visite` varchar(100) NOT NULL,
  `tindakan` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_konseling`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `koreksi_stok`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `koreksi_stok` (
  `id_koreksi` int(11) NOT NULL AUTO_INCREMENT,
  `kd_barang` varchar(50) NOT NULL,
  `tgl` date NOT NULL,
  `nm_kbarang` varchar(100) NOT NULL,
  `stok_barangawal` double NOT NULL,
  `selisih_tx` double NOT NULL,
  `stok_baru` double NOT NULL,
  `ket` text NOT NULL,
  PRIMARY KEY (`id_koreksi`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) NOT NULL,
  `petugas` varchar(255) NOT NULL,
  `aksi` varchar(255) NOT NULL,
  `waktu` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `meso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meso` (
  `id_meso` int(11) NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int(11) NOT NULL,
  `kode_sumber_data` varchar(50) DEFAULT NULL,
  `nama_singkat` varchar(100) DEFAULT NULL,
  `umur` varchar(20) DEFAULT NULL,
  `suku` varchar(50) DEFAULT NULL,
  `berat_badan` varchar(20) DEFAULT NULL,
  `pekerjaan` varchar(100) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `status_hamil` enum('hamil','tidak_hamil','tidak_tahu') DEFAULT NULL,
  `penyakit_utama` text DEFAULT NULL,
  `gangguan_ginjal` tinyint(1) DEFAULT 0,
  `gangguan_hati` tinyint(1) DEFAULT 0,
  `alergi` tinyint(1) DEFAULT 0,
  `kondisi_medis_lain` tinyint(1) DEFAULT 0,
  `kondisi_medis_lain_ket` varchar(255) DEFAULT NULL,
  `kesudahan_penyakit` enum('sembuh','sembuh_gejala_sisa','belum_sembuh','meninggal','tidak_tahu') DEFAULT NULL,
  `manifestasi_eso` text DEFAULT NULL,
  `masalah_mutu_produk` text DEFAULT NULL,
  `tanggal_mula_eso` date DEFAULT NULL,
  `kesudahan_eso` enum('sembuh','sembuh_gejala_sisa','belum_sembuh','meninggal','tidak_tahu') DEFAULT NULL,
  `riwayat_eso` text DEFAULT NULL,
  `data_obat` text DEFAULT NULL,
  `keterangan_tambahan` text DEFAULT NULL,
  `data_laboratorium` text DEFAULT NULL,
  `tanggal_pemeriksaan_lab` date DEFAULT NULL,
  `tanggal_laporan` date DEFAULT NULL,
  `nama_pelapor` varchar(100) DEFAULT NULL,
  `tanda_tangan_pelapor` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_meso`),
  KEY `id_pelanggan` (`id_pelanggan`),
  CONSTRAINT `fk_meso_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `namashift`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `namashift` (
  `id_shift` int(11) NOT NULL AUTO_INCREMENT,
  `shift` int(11) NOT NULL,
  `nama_shift` varchar(10) NOT NULL,
  PRIMARY KEY (`id_shift`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_online`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_online` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `status` varchar(255) NOT NULL,
  `kode_pesanan` varchar(255) DEFAULT NULL,
  `tipe_layanan` enum('Dikirim ke alamat','Ambil di toko') DEFAULT NULL,
  `layanan_pengiriman` varchar(255) DEFAULT NULL,
  `tipe_pembayaran` varchar(30) DEFAULT NULL,
  `diskon` int(11) DEFAULT NULL,
  `total_harga` double DEFAULT NULL,
  `promo_id` bigint(20) unsigned DEFAULT NULL,
  `nama_promo` varchar(255) DEFAULT NULL,
  `nilai_diskon_promo` decimal(5,2) DEFAULT NULL,
  `total_diskon` double DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_tlp` varchar(13) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `midtrans_order_id` int(11) DEFAULT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `petugas_approval` varchar(255) DEFAULT NULL,
  `waktu_approval` timestamp NULL DEFAULT NULL,
  `image` text DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_online_user_id_foreign` (`user_id`),
  CONSTRAINT `order_online_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `order_online_item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_online_item` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `produk_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `harga` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_online_item_order_id_foreign` (`order_id`),
  KEY `order_online_item_produk_id_foreign` (`produk_id`),
  CONSTRAINT `order_online_item_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `order_online` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `orders` (
  `id_trbmasuk` int(11) NOT NULL AUTO_INCREMENT,
  `id_resto` varchar(11) NOT NULL,
  `kd_trbmasuk` varchar(100) NOT NULL,
  `tgl_trbmasuk` date NOT NULL,
  `petugas` varchar(100) NOT NULL,
  `id_supplier` int(11) NOT NULL,
  `nm_supplier` varchar(50) NOT NULL,
  `tlp_supplier` varchar(50) NOT NULL,
  `alamat_trbmasuk` text NOT NULL,
  `ttl_trbmasuk` double NOT NULL,
  `dp_bayar` double NOT NULL,
  `sisa_bayar` double NOT NULL,
  `ket_trbmasuk` text NOT NULL,
  `tandatangan` enum('TIDAK','YA') NOT NULL DEFAULT 'TIDAK',
  `masuk` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_trbmasuk`),
  UNIQUE KEY `kd_trbmasuk` (`kd_trbmasuk`),
  KEY `idx_orders_masuk` (`masuk`),
  KEY `idx_orders_id_resto` (`id_resto`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_kdbm_after_order` AFTER INSERT ON `orders` FOR EACH ROW UPDATE kdbm
    SET stt_kdbm = 'OFF'
    WHERE kd_trbmasuk = NEW.kd_trbmasuk */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `ordersdetail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ordersdetail` (
  `id_dtrbmasuk` int(11) NOT NULL AUTO_INCREMENT,
  `kd_trbmasuk` varchar(100) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `kd_barang` varchar(50) NOT NULL,
  `nmbrg_dtrbmasuk` varchar(100) NOT NULL,
  `qty_dtrbmasuk` double NOT NULL,
  `sat_dtrbmasuk` varchar(30) NOT NULL,
  `hnasat_dtrbmasuk` double NOT NULL,
  `diskon` double NOT NULL,
  `konversi` int(11) NOT NULL,
  `hrgsat_dtrbmasuk` double NOT NULL,
  `hrgjual_dtrbmasuk` double NOT NULL,
  `hrgttl_dtrbmasuk` double NOT NULL,
  `qtygrosir_dtrbmasuk` double NOT NULL,
  `satgrosir_dtrbmasuk` varchar(30) NOT NULL,
  `no_batch` varchar(100) NOT NULL,
  `exp_date` date NOT NULL DEFAULT '2028-01-01',
  `masuk` enum('0','1','2') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_dtrbmasuk`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ordersdetail_hist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ordersdetail_hist` (
  `id_dtrbmasuk` int(11) NOT NULL AUTO_INCREMENT,
  `kd_trbmasuk` varchar(100) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `kd_barang` varchar(50) NOT NULL,
  `nmbrg_dtrbmasuk` varchar(100) NOT NULL,
  `qty_dtrbmasuk` double NOT NULL,
  `sat_dtrbmasuk` varchar(30) NOT NULL,
  `hnasat_dtrbmasuk` double NOT NULL,
  `diskon` double NOT NULL,
  `konversi` int(11) NOT NULL,
  `hrgsat_dtrbmasuk` double NOT NULL,
  `hrgjual_dtrbmasuk` double NOT NULL,
  `hrgttl_dtrbmasuk` double NOT NULL,
  `qtygrosir_dtrbmasuk` double NOT NULL,
  `satgrosir_dtrbmasuk` varchar(30) NOT NULL,
  `no_batch` varchar(100) NOT NULL,
  `exp_date` date NOT NULL DEFAULT '2028-01-01',
  `masuk` enum('0','1') NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_dtrbmasuk`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `page_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `page_visits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `page` varchar(255) NOT NULL DEFAULT 'home-page',
  `ip_address` varchar(255) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `page_visits_page_created_at_index` (`page`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pelanggan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT,
  `nm_pelanggan` varchar(100) NOT NULL,
  `jenis_kelamin` enum('PRIA','WANITA') NOT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `tlp_pelanggan` varchar(30) NOT NULL,
  `alamat_pelanggan` text NOT NULL,
  `ket_pelanggan` text NOT NULL,
  `unit` int(11) NOT NULL DEFAULT 0,
  `total_poin` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_pelanggan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pio` (
  `id_pio` int(11) NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int(11) NOT NULL,
  `no_pio` varchar(50) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `waktu` time DEFAULT NULL,
  `metode` enum('Lisan','Tertulis','Telepon') DEFAULT NULL,
  `nama_penanya` varchar(255) DEFAULT NULL,
  `no_telp_penanya` varchar(50) DEFAULT NULL,
  `status_penanya` enum('Pasien','Keluarga Pasien','Petugas Kesehatan') DEFAULT NULL,
  `status_penanya_ket` varchar(255) DEFAULT NULL COMMENT 'instansi/jabatan untuk Petugas Kesehatan',
  `umur_pasien` int(11) DEFAULT NULL,
  `tinggi_pasien` int(11) DEFAULT NULL,
  `berat_pasien` int(11) DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `kehamilan` tinyint(1) DEFAULT 0,
  `kehamilan_minggu` int(11) DEFAULT NULL,
  `menyusui` tinyint(1) DEFAULT 0,
  `uraian_pertanyaan` text DEFAULT NULL,
  `jenis_pertanyaan_identifikasi_obat` tinyint(1) DEFAULT 0,
  `jenis_pertanyaan_stabilitas` tinyint(1) DEFAULT 0,
  `jenis_pertanyaan_farmakokinetika` tinyint(1) DEFAULT 0,
  `jenis_pertanyaan_interaksi_obat` tinyint(1) DEFAULT 0,
  `jenis_pertanyaan_dosis` tinyint(1) DEFAULT 0,
  `jenis_pertanyaan_farmakodinamika` tinyint(1) DEFAULT 0,
  `jenis_pertanyaan_harga_obat` tinyint(1) DEFAULT 0,
  `jenis_pertanyaan_keracunan` tinyint(1) DEFAULT 0,
  `jenis_pertanyaan_ketersediaan_obat` tinyint(1) DEFAULT 0,
  `jenis_pertanyaan_kontra_indikasi` tinyint(1) DEFAULT 0,
  `jenis_pertanyaan_efek_samping` tinyint(1) DEFAULT 0,
  `jenis_pertanyaan_cara_pemakaian` tinyint(1) DEFAULT 0,
  `jenis_pertanyaan_penggunaan_terapeutik` tinyint(1) DEFAULT 0,
  `jenis_pertanyaan_lain_lain` tinyint(1) DEFAULT 0,
  `jenis_pertanyaan_lain_lain_ket` varchar(255) DEFAULT NULL,
  `jawaban` text DEFAULT NULL,
  `referensi` text DEFAULT NULL,
  `penyampaian_jawaban` enum('Segera','Dalam 24 jam','Lebih dari 24 jam') DEFAULT NULL,
  `apoteker_penjawab` varchar(255) DEFAULT NULL,
  `tanggal_jawab` date DEFAULT NULL,
  `waktu_jawab` time DEFAULT NULL,
  `metode_jawab` enum('Lisan','Tertulis','Telepon') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `created_by` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_pio`),
  KEY `id_pelanggan` (`id_pelanggan`),
  CONSTRAINT `fk_pio_pelanggan` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `poin_pelanggan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `poin_pelanggan` (
  `id_poin` int(11) NOT NULL AUTO_INCREMENT,
  `nm_outlet` varchar(100) NOT NULL,
  `is_outlet` enum('ya','no') NOT NULL,
  `min_penjualan` int(11) NOT NULL,
  `is_kelipatan` enum('ya','no') NOT NULL,
  `poin_pelanggan` int(11) NOT NULL,
  `is_active` enum('ya','no') NOT NULL,
  PRIMARY KEY (`id_poin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `promos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `promos` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama_promo` varchar(255) NOT NULL,
  `tanggal_awal` date NOT NULL,
  `tanggal_akhir` date NOT NULL,
  `nilai_diskon` decimal(5,2) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pto` (
  `id_pto` int(11) NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int(11) NOT NULL,
  `nm_pelanggan` varchar(120) DEFAULT NULL,
  `jenis_kelamin` varchar(30) DEFAULT NULL,
  `umur` varchar(30) DEFAULT NULL,
  `alamat_pelanggan` text DEFAULT NULL,
  `tlp_pelanggan` varchar(30) DEFAULT NULL,
  `tanggal_1` date DEFAULT NULL,
  `catatan_1` text DEFAULT NULL,
  `obat_1` text DEFAULT NULL,
  `masalah_1` text DEFAULT NULL,
  `tindak_1` text DEFAULT NULL,
  `tanggal_2` date DEFAULT NULL,
  `catatan_2` text DEFAULT NULL,
  `obat_2` text DEFAULT NULL,
  `masalah_2` text DEFAULT NULL,
  `tindak_2` text DEFAULT NULL,
  `tempat_ttd` varchar(120) DEFAULT NULL,
  `tanggal_ttd` date DEFAULT NULL,
  `created_by` varchar(120) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_pto`),
  KEY `idx_pto_pelanggan` (`id_pelanggan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rekapso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rekapso` (
  `id_rekap` int(11) NOT NULL AUTO_INCREMENT,
  `stok_awal` int(11) NOT NULL,
  `petugas` varchar(100) NOT NULL,
  `waktu` date NOT NULL,
  PRIMARY KEY (`id_rekap`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `riwayat_pelanggan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `riwayat_pelanggan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_pelanggan` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  `tgl` date NOT NULL,
  `diagnosa` text DEFAULT NULL,
  `foto` text NOT NULL,
  `foto2` text NOT NULL,
  `tindakan` text DEFAULT NULL,
  `followup` text DEFAULT NULL,
  `tgl_followup` datetime DEFAULT NULL,
  `followup_by` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_riwayat_pelanggan_id_admin` (`id_admin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `riwayat_pelanggan_obat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `riwayat_pelanggan_obat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_riwayat` int(11) NOT NULL,
  `kd_barang` bigint(20) NOT NULL,
  `nm_barang` varchar(100) NOT NULL,
  `aturan_pakai` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_rpo_id_riwayat` (`id_riwayat`),
  KEY `idx_rpo_kd_barang` (`kd_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `satuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `satuan` (
  `id_satuan` int(11) NOT NULL AUTO_INCREMENT,
  `nm_satuan` varchar(50) NOT NULL,
  `deskripsi` varchar(250) NOT NULL,
  PRIMARY KEY (`id_satuan`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `setheader`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setheader` (
  `id_setheader` int(11) NOT NULL AUTO_INCREMENT,
  `satu` varchar(111) NOT NULL DEFAULT 'Nama Apotek',
  `dua` varchar(111) NOT NULL DEFAULT 'Alamat Jalan',
  `tiga` varchar(111) NOT NULL DEFAULT 'Nama Kelurahan',
  `empat` varchar(111) NOT NULL DEFAULT 'Nama Apoteker',
  `lima` varchar(111) NOT NULL DEFAULT 'No izin Apotek ',
  `enam` varchar(111) NOT NULL DEFAULT 'no telp apotek',
  `tujuh` varchar(111) DEFAULT 'SIPA APOTEK',
  `delapan` varchar(100) NOT NULL DEFAULT 'Terima Kasih Semoga Tetap Jadi Langganan',
  `sembilan` varchar(100) NOT NULL DEFAULT 'PERHATIAN !!!',
  `sepuluh` varchar(100) NOT NULL DEFAULT 'Barang yang sudah dibeli tidak dapat ditukar',
  `sebelas` varchar(100) NOT NULL DEFAULT '',
  `duabelas` varchar(100) NOT NULL DEFAULT 'belum jelas',
  `tigabelas` varchar(100) NOT NULL DEFAULT 'Kota Apotek',
  `empatbelas` int(11) NOT NULL,
  `logo` text NOT NULL,
  `tandatangan` text NOT NULL,
  PRIMARY KEY (`id_setheader`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `soal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `soal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_soal` int(11) DEFAULT NULL,
  `pertanyaan` text NOT NULL,
  `opsi_a` varchar(255) NOT NULL,
  `opsi_b` varchar(255) NOT NULL,
  `opsi_c` varchar(255) NOT NULL,
  `jawaban_benar` char(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_soal_id_soal` (`id_soal`),
  CONSTRAINT `fk_soal_soal_header` FOREIGN KEY (`id_soal`) REFERENCES `soal_header` (`id_soal`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `soal_header`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `soal_header` (
  `id_soal` int(11) NOT NULL AUTO_INCREMENT,
  `nm_ujian` varchar(100) NOT NULL,
  `durasi` int(11) NOT NULL,
  PRIMARY KEY (`id_soal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `stok_opname`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stok_opname` (
  `id_stok_opname` int(11) NOT NULL AUTO_INCREMENT,
  `id_barang` int(11) NOT NULL,
  `kd_barang` varchar(100) NOT NULL,
  `stok_sistem` double NOT NULL,
  `stok_fisik` double NOT NULL,
  `exp_date` date NOT NULL,
  `jml` int(11) NOT NULL,
  `selisih` double NOT NULL,
  `hrgsat_barang` double NOT NULL,
  `ttl_hrgbrg` double NOT NULL,
  `tgl_current` datetime NOT NULL,
  `tgl_stokopname` date NOT NULL,
  `shift` int(11) NOT NULL,
  `id_admin` int(11) NOT NULL,
  PRIMARY KEY (`id_stok_opname`),
  KEY `idx_stok_opname_kd_barang_tgl` (`kd_barang`,`tgl_stokopname`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supplier` (
  `id_supplier` int(11) NOT NULL AUTO_INCREMENT,
  `nm_supplier` varchar(100) NOT NULL,
  `tlp_supplier` varchar(30) NOT NULL,
  `alamat_supplier` text NOT NULL,
  `ket_supplier` text NOT NULL,
  PRIMARY KEY (`id_supplier`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trbmasuk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trbmasuk` (
  `id_trbmasuk` int(11) NOT NULL AUTO_INCREMENT,
  `id_resto` varchar(11) NOT NULL,
  `petugas` varchar(30) NOT NULL,
  `kd_trbmasuk` varchar(100) NOT NULL,
  `kd_orders` varchar(100) NOT NULL,
  `tgl_trbmasuk` date NOT NULL,
  `id_supplier` int(11) NOT NULL,
  `nm_supplier` varchar(50) NOT NULL,
  `tlp_supplier` varchar(50) NOT NULL,
  `alamat_trbmasuk` text NOT NULL,
  `ttl_trbmasuk` double NOT NULL,
  `dp_bayar` double NOT NULL,
  `sisa_bayar` double NOT NULL,
  `ket_trbmasuk` text NOT NULL,
  `jatuhtempo` varchar(20) NOT NULL,
  `carabayar` varchar(20) NOT NULL DEFAULT 'LUNAS',
  `jenis` enum('nonpbf','pbf') NOT NULL,
  `tgl_lunas` date NOT NULL,
  `petugas_lunas` varchar(100) NOT NULL,
  PRIMARY KEY (`id_trbmasuk`),
  UNIQUE KEY `kd_trbmasuk` (`kd_trbmasuk`),
  KEY `idx_trbmasuk_idresto_id` (`id_resto`,`id_trbmasuk`),
  KEY `idx_trbmasuk_idresto_kd` (`id_resto`,`kd_trbmasuk`),
  KEY `idx_trbmasuk_idresto_tgl` (`id_resto`,`tgl_trbmasuk`),
  KEY `idx_trbmasuk_idresto_supplier` (`id_resto`,`nm_supplier`),
  KEY `idx_trbmasuk_idresto_carabayar` (`id_resto`,`carabayar`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_kdbm_after_trbmasuk` AFTER INSERT ON `trbmasuk` FOR EACH ROW UPDATE kdbm
    SET stt_kdbm = 'OFF'
    WHERE kd_trbmasuk = NEW.kd_trbmasuk */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `trbmasuk_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trbmasuk_detail` (
  `id_dtrbmasuk` int(11) NOT NULL AUTO_INCREMENT,
  `kd_trbmasuk` varchar(100) NOT NULL,
  `kd_orders` varchar(100) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `kd_barang` varchar(50) NOT NULL,
  `nmbrg_dtrbmasuk` varchar(100) NOT NULL,
  `qty_dtrbmasuk` double NOT NULL,
  `sat_dtrbmasuk` varchar(30) NOT NULL,
  `qty_grosir` int(11) NOT NULL,
  `satgrosir_dtrbmasuk` varchar(30) NOT NULL,
  `hnasat_dtrbmasuk` double NOT NULL,
  `diskon` double NOT NULL,
  `konversi` int(11) NOT NULL,
  `hrgsat_dtrbmasuk` double NOT NULL,
  `hrgjual_dtrbmasuk` double NOT NULL,
  `hrgttl_dtrbmasuk` double NOT NULL,
  `no_batch` varchar(100) NOT NULL,
  `exp_date` date NOT NULL DEFAULT '2028-01-01',
  `waktu` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tipe` int(11) NOT NULL,
  `tipe_barang` enum('reguler','bonus') NOT NULL DEFAULT 'reguler',
  PRIMARY KEY (`id_dtrbmasuk`),
  KEY `fk_barangmasuk` (`id_barang`),
  KEY `idx_trbmasuk_detail_kd_barang` (`kd_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trbmasuk_detail_hist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trbmasuk_detail_hist` (
  `id_dtrbmasuk` int(11) NOT NULL AUTO_INCREMENT,
  `kd_trbmasuk` varchar(100) NOT NULL,
  `kd_orders` varchar(100) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `kd_barang` varchar(50) NOT NULL,
  `nmbrg_dtrbmasuk` varchar(100) NOT NULL,
  `qty_dtrbmasuk` double NOT NULL,
  `sat_dtrbmasuk` varchar(30) NOT NULL,
  `qty_grosir` int(11) NOT NULL,
  `satgrosir_dtrbmasuk` varchar(30) NOT NULL,
  `hnasat_dtrbmasuk` double NOT NULL,
  `diskon` double NOT NULL,
  `konversi` int(11) NOT NULL,
  `hrgsat_dtrbmasuk` double NOT NULL,
  `hrgjual_dtrbmasuk` double NOT NULL,
  `hrgttl_dtrbmasuk` double NOT NULL,
  `no_batch` varchar(100) NOT NULL,
  `exp_date` date NOT NULL DEFAULT '2028-01-01',
  `waktu` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tipe` int(11) NOT NULL,
  PRIMARY KEY (`id_dtrbmasuk`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trkasir`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trkasir` (
  `id_trkasir` int(11) NOT NULL AUTO_INCREMENT,
  `kd_trkasir` varchar(100) NOT NULL,
  `id_user` int(11) NOT NULL,
  `petugas` varchar(100) NOT NULL,
  `shift` int(11) NOT NULL,
  `tgl_trkasir` date NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `nm_pelanggan` varchar(100) NOT NULL,
  `tlp_pelanggan` varchar(50) NOT NULL,
  `alamat_pelanggan` text NOT NULL,
  `kodetx` varchar(20) DEFAULT NULL,
  `ttl_trkasir` double NOT NULL,
  `dp_bayar` double DEFAULT NULL,
  `diskon1` double DEFAULT NULL,
  `diskon2` double DEFAULT NULL,
  `sisa_bayar` double DEFAULT NULL,
  `ket_trkasir` text DEFAULT NULL,
  `id_carabayar` int(11) NOT NULL DEFAULT 1,
  `jenistx` int(11) NOT NULL,
  `tipetx` int(11) NOT NULL DEFAULT 1,
  `waktu_trx` datetime DEFAULT NULL,
  `poin_awal` int(11) NOT NULL,
  `tambahan_poin` int(11) NOT NULL,
  `redeem_poin` int(11) NOT NULL,
  PRIMARY KEY (`id_trkasir`),
  UNIQUE KEY `kd_trkasir` (`kd_trkasir`),
  KEY `idx_trkasir_shift_tgl_carabayar_kd` (`shift`,`tgl_trkasir`,`id_carabayar`,`kd_trkasir`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `update_kdtk_after_trkasir` AFTER INSERT ON `trkasir` FOR EACH ROW UPDATE kdtk
    SET stt_kdtk = 'OFF'
    WHERE kd_trkasir = NEW.kd_trkasir */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
DROP TABLE IF EXISTS `trkasir_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trkasir_detail` (
  `id_dtrkasir` int(11) NOT NULL AUTO_INCREMENT,
  `kd_trkasir` varchar(100) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `kd_barang` varchar(50) NOT NULL,
  `nmbrg_dtrkasir` varchar(100) NOT NULL,
  `qty_dtrkasir` double NOT NULL,
  `sat_dtrkasir` varchar(30) NOT NULL,
  `hrgjual_dtrkasir` double NOT NULL,
  `disc` int(2) DEFAULT NULL,
  `modal` double NOT NULL,
  `profit` double NOT NULL,
  `hrgttl_dtrkasir` double NOT NULL,
  `no_batch` varchar(20) DEFAULT NULL,
  `exp_date` date DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tipe` int(11) NOT NULL,
  `komisi` int(11) DEFAULT NULL,
  `idadmin` int(11) NOT NULL,
  `tipetx` int(11) NOT NULL DEFAULT 1,
  `resep` enum('YA','TIDAK') NOT NULL DEFAULT 'TIDAK',
  `kd_bundle` varchar(50) NOT NULL,
  `nm_bundle` varchar(100) NOT NULL,
  PRIMARY KEY (`id_dtrkasir`),
  KEY `idx_trkasir_detail_kd_barang` (`kd_barang`),
  KEY `idx_trkasir_detail_kdtrkasir_nmbrg` (`kd_trkasir`,`nmbrg_dtrkasir`),
  KEY `idx_trkasir_detail_id_barang` (`id_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trkasir_detail_hist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trkasir_detail_hist` (
  `id_dtrkasir` int(11) NOT NULL AUTO_INCREMENT,
  `kd_trkasir` varchar(100) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `kd_barang` varchar(50) NOT NULL,
  `nmbrg_dtrkasir` varchar(100) NOT NULL,
  `qty_dtrkasir` double NOT NULL,
  `sat_dtrkasir` varchar(30) NOT NULL,
  `hrgjual_dtrkasir` double NOT NULL,
  `disc` int(2) DEFAULT NULL,
  `modal` double NOT NULL,
  `profit` double NOT NULL,
  `hrgttl_dtrkasir` double NOT NULL,
  `no_batch` varchar(20) NOT NULL,
  `exp_date` date DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `tipe` int(11) NOT NULL,
  `komisi` int(11) DEFAULT NULL,
  `idadmin` int(11) NOT NULL,
  `tipetx_asal` int(11) NOT NULL DEFAULT 1,
  `tipetx_hapus` int(11) NOT NULL DEFAULT 1,
  `waktu_hapus` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_admin_hapus` int(11) DEFAULT NULL,
  `resep` enum('TIDAK','YA') NOT NULL DEFAULT 'TIDAK',
  `kd_bundle` varchar(50) NOT NULL,
  `nm_bundle` varchar(100) NOT NULL,
  PRIMARY KEY (`id_dtrkasir`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trkasir_detail_ubah_qty`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trkasir_detail_ubah_qty` (
  `id_log` bigint(20) NOT NULL AUTO_INCREMENT,
  `kd_trkasir` varchar(100) NOT NULL,
  `id_dtrkasir` int(11) NOT NULL,
  `kd_barang` varchar(50) NOT NULL,
  `nmbrg_dtrkasir` varchar(100) NOT NULL,
  `qty_sebelum` double NOT NULL,
  `qty_sesudah` double NOT NULL,
  `hrgttl_sebelum` double NOT NULL,
  `hrgttl_sesudah` double NOT NULL,
  `tipetx` int(11) NOT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_log`),
  KEY `idx_trkasir_detail_ubah_qty_kd_trkasir` (`kd_trkasir`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trkasir_restore`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trkasir_restore` (
  `id_butrkasir` int(11) NOT NULL AUTO_INCREMENT,
  `kd_trkasir` varchar(100) NOT NULL,
  `petugas` varchar(100) NOT NULL,
  `shift` int(11) NOT NULL,
  `tgl_trkasir` date NOT NULL,
  `nm_pelanggan` varchar(100) NOT NULL,
  `tlp_pelanggan` varchar(50) NOT NULL,
  `alamat_pelanggan` text NOT NULL,
  `ttl_trkasir` double NOT NULL,
  `dp_bayar` double DEFAULT NULL,
  `diskon1` double DEFAULT NULL,
  `diskon2` double DEFAULT NULL,
  `sisa_bayar` double DEFAULT NULL,
  `ket_trkasir` text DEFAULT NULL,
  `id_carabayar` int(11) NOT NULL,
  `id_barang` int(11) NOT NULL,
  `kd_barang` varchar(50) NOT NULL,
  `nmbrg_dtrkasir` varchar(100) NOT NULL,
  `qty_dtrkasir` double NOT NULL,
  `sat_dtrkasir` varchar(30) NOT NULL,
  `hrgjual_dtrkasir` double NOT NULL,
  `hrgttl_dtrkasir` double NOT NULL,
  `id_dtrkasir` int(11) DEFAULT NULL,
  `disc` int(2) DEFAULT NULL,
  `resep` varchar(10) DEFAULT NULL,
  `modal` double DEFAULT NULL,
  `profit` double DEFAULT NULL,
  `no_batch` varchar(20) DEFAULT NULL,
  `exp_date` date DEFAULT NULL,
  `waktu` timestamp NULL DEFAULT NULL,
  `tipe` int(11) DEFAULT NULL,
  `komisi` int(11) DEFAULT NULL,
  `idadmin` int(11) DEFAULT NULL,
  `kd_bundle` varchar(50) DEFAULT NULL,
  `nm_bundle` varchar(100) DEFAULT NULL,
  `tipetx` int(11) NOT NULL DEFAULT 1,
  `waktu_hapus` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_admin_hapus` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_pelanggan` int(11) DEFAULT NULL,
  `kodetx` varchar(20) DEFAULT NULL,
  `jenistx` int(11) DEFAULT NULL,
  `waktu_trx` datetime DEFAULT NULL,
  `poin_awal` int(11) DEFAULT NULL,
  `tambahan_poin` int(11) DEFAULT NULL,
  `redeem_poin` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_butrkasir`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `trx_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `trx_orders` (
  `id_trx_ordrers` int(11) NOT NULL AUTO_INCREMENT,
  `kd_trbmasuk` varchar(100) NOT NULL,
  `kd_orders` varchar(100) NOT NULL,
  `keterangan` text NOT NULL,
  PRIMARY KEY (`id_trx_ordrers`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ujian_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ujian_progress` (
  `id_progress` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_admin` int(11) NOT NULL,
  `username` varchar(100) DEFAULT NULL,
  `nama_lengkap` varchar(150) DEFAULT NULL,
  `ujian_id` int(11) NOT NULL,
  `nama_ujian` varchar(150) DEFAULT NULL,
  `jawaban_json` longtext DEFAULT NULL,
  `waktu_mulai` datetime DEFAULT NULL,
  `waktu_update` datetime DEFAULT NULL,
  PRIMARY KEY (`id_progress`),
  UNIQUE KEY `uniq_ujian_progress_admin_ujian` (`id_admin`,`ujian_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_login_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_login_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `login_time` datetime NOT NULL,
  `logout_time` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_login_time` (`login_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `no_tlp` varchar(255) DEFAULT NULL,
  `referal_admin_id` int(11) DEFAULT NULL,
  `komisi_status` enum('belum','lunas') NOT NULL DEFAULT 'belum',
  `waktu_komisi_lunas` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `waktukerja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `waktukerja` (
  `id_shift` int(11) NOT NULL AUTO_INCREMENT,
  `petugasbuka` varchar(100) NOT NULL,
  `petugastutup` varchar(100) NOT NULL,
  `shift` varchar(5) NOT NULL,
  `tanggal` varchar(10) NOT NULL,
  `waktubuka` time NOT NULL,
  `waktututup` time NOT NULL,
  `saldoawal` int(11) NOT NULL,
  `saldoakhir` int(11) NOT NULL,
  `status` varchar(3) NOT NULL,
  PRIMARY KEY (`id_shift`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `zataktif`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `zataktif` (
  `id_zataktif` int(11) NOT NULL AUTO_INCREMENT,
  `nm_zataktif` varchar(250) NOT NULL,
  `indikasi` varchar(250) NOT NULL,
  `aturanpakai` varchar(250) NOT NULL,
  `saran` varchar(250) NOT NULL,
  `user` varchar(250) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_zataktif`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'2025_07_11_015213_create_categories_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'2025_07_12_014646_create_articles_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'2025_07_12_052310_create_order_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'2025_07_12_052335_create_order_item_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'2025_08_04_135241_create_company_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'2025_08_06_080910_create_banners_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'2026_08_22_100500_create_cache_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'2026_08_22_100509_create_jobs_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_08_22_100510_create_failed_jobs_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_08_22_101500_create_sessions_table',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_08_22_232356_add_bukti_pembayaran_to_order_online_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_08_22_232357_add_qris_image_to_company_settings_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2026_08_22_235919_add_petugas_approval_to_order_online_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2026_08_23_002710_make_image_catatan_nullable_on_order_online_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2026_08_24_065348_create_page_visits_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2026_08_24_081513_add_foto_to_admin_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2026_08_24_110000_add_notification_sound_to_company_settings_table',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2026_08_28_185940_create_promos_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2026_08_28_190904_add_promo_diskon_to_order_online_table',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2026_08_28_231517_add_default_values_to_inventory_module_columns',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2026_08_29_004000_create_pto_table_if_missing',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2026_08_30_000000_create_riwayat_pelanggan_tables_if_missing',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2026_09_06_124302_add_t60_gr_to_barang_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2026_09_06_172012_add_lapstokopname_flag_to_admin_table',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2026_09_08_202813_add_referal_admin_id_to_users_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2026_09_08_204846_add_komisi_status_to_users_table',14);
