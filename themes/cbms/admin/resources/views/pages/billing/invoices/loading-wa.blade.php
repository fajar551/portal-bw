<!DOCTYPE html>
<html>

<head>
    <title>Mengirim Pesan WA</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background-color: #f5f5f5;
        }

        .progress-container {
            width: 80%;
            max-width: 500px;
            text-align: center;
        }

        .progress {
            width: 100%;
            height: 20px;
            background-color: #f0f0f0;
            border-radius: 10px;
            overflow: hidden;
            margin: 20px 0;
        }

        .progress-bar {
            width: 0%;
            height: 100%;
            background-color: #4CAF50;
            transition: width 0.5s;
        }

        .message {
            color: #666;
            margin-top: 20px;
        }

        .result {
            margin-top: 20px;
            font-weight: bold;
            display: none;
        }

        .success {
            color: #4CAF50;
        }

        .failed {
            color: #f44336;
        }
        .completed {
            animation: none !important; /* Menghentikan animasi ketika selesai */
        }
    </style>
</head>

<body>
    <div class="progress-container">
        <h2>Sedang Mengirim Pesan WhatsApp</h2>
        <div class="progress">
            <div class="progress-bar"></div>
        </div>
        <p class="message">Proses ini membutuhkan waktu beberapa menit.<br>Mohon jangan tutup halaman ini.</p>
        <div class="result"></div>
    </div>

    <script>
      $(document).ready(function() {
          let progressInterval;
          const urlParams = new URLSearchParams(window.location.search);
          const currentUrl = window.location.href;
          const baseUrl = currentUrl.split('?')[0];

          // Fungsi untuk menghentikan progress bar
          function stopProgress() {
              clearInterval(progressInterval);
              $('.progress-bar').addClass('completed');
          }

          // Fungsi untuk update UI berdasarkan response
          function updateUI(response) {
              stopProgress();
              $('.progress-bar').css('width', '100%');
              $('.message').hide();

              if (response.includes("Berhasil: 0, Gagal:")) {
                  // Jika semua gagal
                  $('.progress-bar').css('background-color', '#f44336');
                  $('.result').addClass('failed');
              } else if (response.includes("Terjadi kesalahan")) {
                  // Jika error sistem
                  $('.progress-bar').css('background-color', '#f44336');
                  $('.result').addClass('failed');
              } else if (response.includes("Berhasil:") && response.includes("Gagal:")) {
                  // Jika ada yang berhasil dan gagal
                  $('.progress-bar').css('background-color', '#ff9800');
                  $('.result').addClass('warning');
              } else {
                  // Jika semua berhasil
                  $('.progress-bar').css('background-color', '#4CAF50');
                  $('.result').addClass('success');
              }

              $('.result').html(response).show();
          }

          // Mulai animasi progress bar
          let width = 0;
          progressInterval = setInterval(function() {
              if (width >= 90) {
                  clearInterval(progressInterval);
              } else {
                  width++;
                  $('.progress-bar').css('width', width + '%');
              }
          }, 500);

          // Kirim request ke endpoint yang sama tanpa parameter show_loading
          const apiUrl = baseUrl + '?' + Array.from(urlParams.entries())
              .filter(([key]) => key !== 'show_loading')
              .map(([key, value]) => `${key}=${value}`)
              .join('&');

          $.ajax({
              url: apiUrl,
              method: 'GET',
              success: function(response) {
                  updateUI(response);
              },
              error: function(xhr, status, error) {
                  stopProgress();
                  $('.progress-bar').css({
                      'width': '100%',
                      'background-color': '#f44336'
                  });
                  $('.message').hide();
                  $('.result').addClass('failed')
                      .html('Terjadi kesalahan: ' + (xhr.responseText || error))
                      .show();
              }
          });
      });
  </script>
</body>

</html>
