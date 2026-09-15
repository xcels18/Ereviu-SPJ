# TODO - Implementasi MFA (TOTP) pada Laravel Breeze

- [ ] Tambah dependency package TOTP + QR code di composer.json
- [ ] Tambah kolom MFA pada tabel users (migration)
- [ ] Update model User untuk atribut MFA + casts/enkripsi
- [ ] Buat service/helper MFA (generate secret, OTP verify, recovery codes)
- [ ] Tambah controller untuk setup/enable/disable/challenge MFA
- [ ] Tambah routes MFA (setup, verify, disable, challenge)
- [ ] Ubah alur login agar MFA aktif diarahkan ke challenge OTP
- [ ] Tambah middleware untuk memastikan MFA tervalidasi di session
- [ ] Tambah UI di profile untuk aktif/nonaktif MFA + QR + recovery codes
- [ ] Tambah halaman input OTP challenge setelah login
- [ ] Update login view bila perlu (pesan/alur)
- [ ] Testing dasar flow login + MFA enable/disable
