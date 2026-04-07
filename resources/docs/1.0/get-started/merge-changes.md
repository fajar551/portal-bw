# Merge Changes

Melakukan perubahan atau improvement pada core.

---

Ketika developer lain melakukan perubahan pada lokal mereka, dan sudah di merge ke branch dev, itu artinya Anda perlu merge perubahan developer lain ke lokal branch Anda. Perhatikan instruksi berikut:

---

## Dapatkan perubahan terbaru
Sebelum Anda pull dari branch dev, pastikan perubahan lokal sudah di commit ke gitserver dan tertuju pada branch Anda. Misal branch gitserver Anda adalah `joko`.
<br>
<br>
kemudian `git pull origin dev`
<br>
<br>
Lakukan merge dengan memeriksa apakah ada *conflict*
<br>
<br>
Perbaiki konflik jika ada, lalu push kembali ke branch `joko`