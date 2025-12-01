// script.js
document.addEventListener('DOMContentLoaded', function(){
  const form = document.getElementById('regForm');
  const toast = document.getElementById('toast');

  // Show toast if redirected with ?success=1 or ?error=...
  const params = new URLSearchParams(location.search);
  if(params.get('success') === '1'){
    toast.textContent = 'Registration submitted successfully';
    toast.style.display = 'block';
    setTimeout(()=> toast.style.display='none', 3000);
  } else if(params.get('error')){
    toast.textContent = decodeURIComponent(params.get('error'));
    toast.style.display = 'block';
    setTimeout(()=> toast.style.display='none', 4200);
  }

  // Basic client-side: let native validation run
  form.addEventListener('submit', function(e){
    if(!form.checkValidity()){
      // allow browser to show built-in messages
      return;
    }
    // normal form submit will proceed to submit.php
  });
});
