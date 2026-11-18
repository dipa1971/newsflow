<!DOCTYPE html>
<html lang="bn">
<head>
<meta charset="utf-8">
<style>
{!! $font !!}
* { margin: 0; padding: 0; box-sizing: border-box; }
html, body { width: 1080px; height: 1350px; overflow: hidden; background: #fff; }
body { font-family: 'CardBangla', 'Noto Sans Bengali', 'Nirmala UI', 'Kalpurush', sans-serif; }
.card { position: relative; width: 1080px; height: 1350px; overflow: hidden; }
.photo { position: absolute; background-size: cover; background-position: center; }
.hl { font-weight: 700; line-height: 1.38; overflow: hidden; }
.logo { height: 80px; width: auto; max-width: 380px; object-fit: contain; }
.site { font-weight: 700; font-size: 34px; }
.date { font-size: 26px; }
@yield('styles')
</style>
</head>
<body>
<div class="card">
@yield('card')
</div>
<script>
(function () {
  // শিরোনাম বাক্সে না ধরলে অক্ষর ছোট করতে থাকে
  function fit() {
    var h = document.getElementById('hl');
    if (!h) { return; }
    var size = parseInt(h.getAttribute('data-max') || '64', 10);
    h.style.fontSize = size + 'px';
    while (h.scrollHeight > h.clientHeight && size > 30) {
      size -= 2;
      h.style.fontSize = size + 'px';
    }
  }
  fit();
  if (document.fonts && document.fonts.ready) { document.fonts.ready.then(fit); }
})();
</script>
</body>
</html>
