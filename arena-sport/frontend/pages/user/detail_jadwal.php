<?php
/**
 * Halaman Detail & Jadwal - Arena Sport
 */
$pageTitle = 'Detail Jadwal';

// DATA SLOT JAM
$slots = [
    ['jam'=>'16.00 - 17.00 WIB','harga'=>150000,'label'=>null,'status'=>'available'],
    ['jam'=>'17.00 - 18.00 WIB','harga'=>150000,'label'=>null,'status'=>'available'],
    ['jam'=>'18.00 - 19.00 WIB','harga'=>180000,'label'=>null,'status'=>'booked'],
    ['jam'=>'19.00 - 20.00 WIB','harga'=>180000,'label'=>'Prime Time','status'=>'selected'],
    ['jam'=>'20.00 - 21.00 WIB','harga'=>180000,'label'=>'Prime Time','status'=>'selected'],
    ['jam'=>'21.00 - 22.00 WIB','harga'=>180000,'label'=>null,'status'=>'available'],
];

// DATA TANGGAL
$tanggal = [
    ['val'=>'2026-10-21','hari'=>'Sen','tgl'=>'21'],
    ['val'=>'2026-10-22','hari'=>'Sel','tgl'=>'22','aktif'=>true],
    ['val'=>'2026-10-23','hari'=>'Rab','tgl'=>'23'],
    ['val'=>'2026-10-24','hari'=>'Kam','tgl'=>'24'],
    ['val'=>'2026-10-25','hari'=>'Jum','tgl'=>'25'],
];

// DATA FASILITAS
$fas = [
    ['icon'=>'layers','label'=>'Lantai','ket'=>'6mm Vinyl Standar'],
    ['icon'=>'lightbulb','label'=>'Penerangan','ket'=>'800 Lux LED'],
    ['icon'=>'ac_unit','label'=>'AC','ket'=>'Central'],
    ['icon'=>'wifi','label'=>'WiFi','ket'=>'Free 50Mbps'],
];

$fasi = ['Locker Room','Shower Air Panas','Kantin','Mushola'];
?>
<?php include '../../components/header.php'; // PATH BENAR: ../../components/ karena di dalam folder pages/user/ ?>

<!-- CONTAINER UTAMA -->
<div class="max-w-lg mx-auto bg-slate-100 min-h-screen">

<!-- HEADER -->
<header class="sticky top-0 z-50 bg-white/95 border-b border-slate-200 shadow-sm">
<div class="max-w-lg mx-auto px-4 py-3 flex items-center justify-between">

<!-- Back Button -->
<button id="btnBack" class="w-10 h-10 flex items-center justify-center rounded-full text-slate-500 hover:bg-slate-100 active:scale-95" aria-label="Kembali">
<span class="material-symbols-outlined text-2xl">arrow_back</span>
</button>

<h1 class="font-bold text-slate-900 text-sm">Detail Jadwal</h1>

<div class="flex gap-1">
<button id="btnShare" class="w-10 h-10 flex items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="Share">
<span class="material-symbols-outlined text-xl">share</span>
</button>
<button id="btnFav" class="w-10 h-10 flex items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="Favorit">
<span class="material-symbols-outlined text-xl">favorite_border</span>
</button>
</div>
</div>

<!-- STEPPER -->
<div class="max-w-lg mx-auto px-4 py-3 bg-white border-t border-slate-100">
<div class="flex items-center justify-between relative max-w-xs mx-auto">
<div class="absolute top-3 left-8 right-8 h-0.5 bg-slate-200 z-0"></div>
<div class="absolute top-3 left-8 w-1/2 h-0.5 bg-primary z-0"></div>

<!-- Step 1 Done -->
<div class="relative z-10 flex flex-col items-center gap-1">
<div class="w-7 h-7 rounded-full bg-primary text-white flex items-center justify-center shadow-sm">
<span class="material-symbols-outlined text-sm">check</span>
</div>
<span class="text-xs text-primary font-bold">1. Rincian</span>
</div>

<!-- Step 2 Active -->
<div class="relative z-10 flex flex-col items-center gap-1">
<div class="w-7 h-7 rounded-full bg-primary text-white flex items-center justify-center shadow-sm ring-4 ring-emerald-100">
<span class="text-xs font-bold">2</span>
</div>
<span class="text-xs text-slate-900 font-bold">2. Jadwal</span>
</div>

<!-- Step 3 Pending -->
<div class="relative z-10 flex flex-col items-center gap-1">
<div class="w-7 h-7 rounded-full bg-slate-200 text-slate-400 flex items-center justify-center">
<span class="text-xs font-bold">3</span>
</div>
<span class="text-xs text-slate-400">3. Bayar</span>
</div>
</div>
</div>
</header>

<!-- MAIN CONTENT -->
<main class="px-4 pb-36">

<!-- HERO IMAGE -->
<div class="relative mb-4 -mx-4">
<img src="../../assets/img/lapangan-futsal-01.webp" alt="Lapangan Futsal" class="w-full h-56 object-cover">
<div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent"></div>

<!-- BADGES ATAS -->
<div class="absolute top-3 left-4 right-4 flex justify-between">
<div class="flex gap-2">
<span class="bg-white/90 text-slate-700 text-xs font-semibold px-3 py-1 rounded-full shadow-sm">Futsal</span>
<span class="bg-primary/90 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">Indoor Vinyl</span>
</div>
<span class="bg-black/60 text-white text-xs px-2 py-1 rounded-full">1/5</span>
</div>
</div>

<!-- CARD INFO VENUE -->
<div class="mb-5 bg-white rounded-2xl shadow-sm p-4">
<div class="flex justify-between items-start mb-2">
<h1 class="text-xl font-extrabold text-slate-900 leading-tight">Champion Futsal Arena</h1>
<span class="bg-slate-100 text-primary text-xs font-bold px-2 py-0.5 rounded-full uppercase">Court A</span>
</div>

<div class="flex items-center gap-2 mb-2 flex-wrap">
<div class="flex items-center gap-1 bg-amber-50 px-2 py-0.5 rounded-full">
<span class="material-symbols-filled text-amber-400 text-base">star</span>
<span class="text-sm font-bold text-amber-800">4.8</span>
</div>
<span class="text-sm text-slate-500">(124 ulasan)</span>
<span class="flex items-center text-xs text-primary font-semibold gap-0.5"><span class="material-symbols-outlined text-xs">verified</span>Terverifikasi</span>
</div>

<div class="flex gap-1.5 text-slate-500">
<span class="material-symbols-outlined text-primary text-base">location_on</span>
<span class="text-sm">Jl. TB Simatupang No.18, Jakarta Selatan</span>
</div>
</div>

<!-- ACCORDION SPESIFIKASI -->
<div class="accordion mb-5" id="accSpec">
<div class="border border-slate-200 rounded-2xl bg-white shadow-sm overflow-hidden">

<h2 class="accordion-header">
<button class="accordion-button collapsed w-full flex items-center gap-2 px-4 py-3 text-sm font-semibold text-slate-900 bg-white hover:bg-slate-50" type="button" data-bs-toggle="collapse" data-bs-target="#specContent" aria-expanded="false">
<span class="material-symbols-outlined text-primary">info</span>
Spesifikasi & Fasilitas
</button>
</h2>

<div id="specContent" class="accordion-collapse collapse" data-bs-parent="#accSpec">
<div class="accordion-body p-4 text-sm text-slate-600 space-y-4">

<div>
<p class="font-semibold text-slate-900 mb-2">Spesifikasi</p>
<div class="grid grid-cols-2 gap-2 mb-3">
<?php foreach($fas as $f): ?>
<div class="flex items-center gap-2 p-2 bg-slate-50 rounded-lg">
<span class="material-symbols-outlined text-primary"><?=$f['icon']?></span>
<div class="text-xs"><span class="text-slate-500"><?=$f['label']?></span><br><span class="text-slate-900 font-medium"><?=$f['ket']?></span></div>
</div>
<?php endforeach; ?>
</div>

<div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800 flex gap-2">
<span class="material-symbols-outlined text-amber-600 mt-0.5">warning</span>
Wajib sepatu futsal sol karet datar
</div>
</div>

<div>
<p class="font-semibold text-slate-900 mb-2">Fasilitas</p>
<div class="grid grid-cols-2 gap-2 text-xs text-slate-600">
<?php foreach($fasi as $f): ?>
<div class="flex items-center gap-1.5"><span class="material-symbols-outlined text-primary">check_circle</span><?=$f?></div>
<?php endforeach; ?>
</div>
</div>
</div>
</div>
</div>
</div>
</div>

<!-- FORM BOOKING -->
<form action="checkout.php" method="get" id="bookingForm">

<!-- PILIH TANGGAL -->
<div class="mb-5">
<p class="text-sm font-semibold text-slate-900 mb-3 flex items-center gap-2">
<span class="material-symbols-outlined text-primary">calendar_month</span>Pilih Tanggal
</p>
<div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
<?php foreach($tanggal as $t):
$aktif = !empty($t['aktif']);
?>
<label class="shrink-0 cursor-pointer">
<input type="radio" name="tanggal" value="<?=$t['val']?>" <?=$aktif?'checked':''?> class="hidden peer">
<div class="flex flex-col items-center min-w-[56px] py-2.5 px-2 rounded-xl border transition-all
peer-checked:bg-primary peer-checked:border-primary peer-checked:text-white
border-slate-200 text-slate-600 hover:border-primary">
<span class="text-[10px] font-semibold uppercase"><?=$t['hari']?></span>
<span class="text-base font-bold"><?=$t['tgl']?></span>
</div>
</label>
<?php endforeach; ?>
</div>
</div>

<!-- PILIH SLOT JAM -->
<div class="mb-5">
<p class="text-sm font-semibold text-slate-900 mb-1 flex items-center gap-2">
<span class="material-symbols-outlined text-primary">schedule</span>Pilih Slot Jam
</p>
<p class="text-xs text-slate-500 mb-3">Pilih beberapa slot sekaligus untuk durasi lebih lama</p>

<div class="flex gap-4 text-xs text-slate-500 mb-3">
<span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-white border border-slate-200 shadow-sm"></span>Tersedia</span>
<span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-primary"></span>Terpilih</span>
<span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-slate-200"></span>Penuh</span>
</div>

<div class="space-y-2" id="slotList">
<?php foreach($slots as $s):
$booked = $s['status'] === 'booked';
$sel = $s['status'] === 'selected';
$harga = 'Rp '.number_format($s['harga'],0,',','.');
?>
<label class="block <?=$booked?'opacity-50 cursor-not-allowed':'cursor-pointer' ?>">
<input type="checkbox" name="slot[]" value="<?=htmlspecialchars($s['jam'])?>" <?=$booked?'disabled':''?> <?=$sel?'checked':''?> class="hidden peer cb-slot" data-harga="<?=$s['harga']?>">
<div class="flex items-center justify-between px-4 py-3.5 rounded-xl border transition-all
<?=$booked?'bg-slate-100 border-slate-200':($sel?'bg-emerald-50 border-primary shadow-md':'bg-white border-slate-200 hover:border-primary')?>">
<div>
<p class="text-sm font-semibold <?=$booked?'line-through text-slate-400':($sel?'text-primary font-extrabold':'text-slate-900')?>">
<?=$s['jam']?>
<?php if($s['label']): ?><span class="ml-1 text-[10px] bg-amber-100 text-amber-700 font-bold px-1.5 py-0.5 rounded-full"><?=$s['label']?></span><?php endif; ?>
</p>
<p class="text-xs <?=$booked?'text-slate-400':($sel?'text-primary font-semibold':'text-slate-500')?>">
<?=$booked?'Penuh':$harga.'/jam' ?>
</p>
</div>
<?php if($booked): ?>
<span class="text-xs text-slate-400 bg-slate-200 px-2 py-1 rounded-full font-semibold">Penuh</span>
<?php elseif($sel): ?>
<span class="material-symbols-filled text-primary text-xl">check_circle</span>
<?php else: ?>
<span class="material-symbols-outlined text-slate-300 text-xl">circle</span>
<?php endif; ?>
</div>
</label>
<?php endforeach; ?>
</div>
</div>

</form>
</main>

<!-- FLOATING BAR -->
<div class="fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-slate-200 shadow-lg">
<div class="max-w-lg mx-auto px-4 py-4 flex items-center justify-between gap-4">
<div>
<p class="text-xs text-slate-500" id="durasiLabel">Durasi: <span id="durasiNum">2 jam</span></p>
<p class="text-xl font-bold text-slate-900" id="totalHarga">Rp 360.000</p>
</div>
<button type="submit" form="bookingForm" id="btnLanjut"
class="bg-primary hover:bg-emerald-700 disabled:bg-slate-200 disabled:cursor-not-allowed
text-white font-semibold px-6 py-3 rounded-xl text-sm transition-all shadow-md active:scale-95 flex items-center gap-2">
Lanjut
<span class="material-symbols-outlined text-lg">arrow_forward</span>
</button>
</div>
</div>

</div>

<!-- JAVASCRIPT -->
<script>
(function(){
var btn=document.getElementById('btnBack');
if(btn) btn.addEventListener('click',function(){ history.back(); });

var cbs=document.querySelectorAll('.cb-slot');
var total=document.getElementById('totalHarga');
var durasi=document.getElementById('durasiNum');

function hitung(){
var sum=0, count=0;
cbs.forEach(function(c){ if(c.checked){ sum+=parseInt(c.dataset.harga); count++; } });
if(total) total.textContent='Rp '+sum.toLocaleString('id-ID');
if(durasi) durasi.textContent=count>0?count+' jam':'0 jam (pilih slot';
}
cbs.forEach(function(c){ c.addEventListener('change',hitung); });
hitung();
})();
</script>

<?php include '../../components/footer.php'; ?>
