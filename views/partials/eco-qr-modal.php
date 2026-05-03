<!-- Ecosystem QR Modal – include in any project list/detail view -->
<?php
$qrGeneratePath = \Core\EcosystemIntegration::route('qr_generate') ?? '/projects/qr/generate';
?>
<div id="ecoQrBackdrop"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.78);z-index:9900;align-items:center;justify-content:center;"
     onclick="if(event.target===this)ecoQrClose()">
    <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,0.1));border-radius:0.875rem;padding:1.5rem;max-width:22rem;width:92%;text-align:center;position:relative;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
            <span style="font-weight:700;font-size:1rem;display:flex;align-items:center;gap:0.4rem;">
                <i class="fas fa-qrcode" style="color:#00f0ff;"></i> QR Code
            </span>
            <button type="button" onclick="ecoQrClose()"
                    style="background:none;border:none;cursor:pointer;color:var(--text-secondary,#8892a6);font-size:1.25rem;line-height:1;padding:0;"
                    aria-label="Close">×</button>
        </div>
        <div id="ecoQrUrl"
             style="font-size:0.7rem;color:var(--text-secondary,#8892a6);word-break:break-all;margin-bottom:0.875rem;text-align:left;"></div>
        <div id="ecoQrContainer"
             style="display:flex;justify-content:center;min-height:9rem;margin-bottom:0.75rem;"></div>
        <div style="display:flex;gap:0.5rem;">
            <button type="button" onclick="ecoQrClose()"
                    style="flex:1;padding:0.5rem;font-size:0.78rem;border-radius:0.375rem;border:1px solid var(--border-color,rgba(255,255,255,0.1));background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);cursor:pointer;font-family:inherit;">
                <i class="fas fa-times"></i> Close
            </button>
            <a id="ecoQrOpenInQrx" href="<?= \Core\View::e($qrGeneratePath) ?>" target="_blank" rel="noopener"
               style="flex:1;padding:0.5rem;font-size:0.78rem;border-radius:0.375rem;border:1px solid #00f0ff;background:#00f0ff;color:#000;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:0.3rem;font-weight:600;font-family:inherit;">
                <i class="fas fa-external-link-alt"></i> Open in QRx
            </a>
        </div>
    </div>
</div>
<script>
(function(){
    var _loaded = false;
    function _loadLib(cb){
        if(window.QRCode){ cb(); return; }
        var s=document.createElement('script');
        s.src='https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
        s.integrity='sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==';
        s.crossOrigin='anonymous';
        s.onload=cb;
        s.onerror=function(){
            var s2=document.createElement('script');
            s2.src='https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js';
            s2.onload=cb;
            document.head.appendChild(s2);
        };
        document.head.appendChild(s);
    }
    window.ecoQrOpen=function(url, label){
        var bd=document.getElementById('ecoQrBackdrop');
        var ct=document.getElementById('ecoQrContainer');
        var ul=document.getElementById('ecoQrUrl');
        var lk=document.getElementById('ecoQrOpenInQrx');
        ct.innerHTML='';
        ul.textContent=url;
        lk.href='<?= \Core\View::e($qrGeneratePath) ?>';
        bd.style.display='flex';
        _loadLib(function(){
            ct.innerHTML='';
            try{
                new QRCode(ct,{text:url,width:172,height:172,colorDark:'#000',colorLight:'#fff',correctLevel:QRCode.CorrectLevel.H});
            }catch(e){}
        });
    };
    window.ecoQrClose=function(){
        var bd=document.getElementById('ecoQrBackdrop');
        if(bd) bd.style.display='none';
        var ct=document.getElementById('ecoQrContainer');
        if(ct) ct.innerHTML='';
    };
    document.addEventListener('keydown',function(e){ if(e.key==='Escape') ecoQrClose(); });
})();
</script>
