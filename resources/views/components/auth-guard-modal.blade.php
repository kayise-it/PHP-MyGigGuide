; <!-- Force Hero Heart non-JS layout -->
<div class="container mx-auto px-6">
    <div class="text-sm border rounded-lg p-4 flex align-middle">
        <svg class="inline-block h-5 w-5 mr-2 text-red-500 fill-current" viewBox="0 0 24 24" stroke="none">
            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
        </svg> <span class='text-gray-800 tracking-wide'>Please <a class="font-medium underline text-purple-700">Login</a> to add this venue to Favorites</span> &nbsp;<div class="inline-flex flex-wrap ml-2"><button class='px-3 py-1 text-xs bg-purple-50 text-purple-800 rounded'>Sign up now</button></div>
    </div>  
</div>
    
<script>
// Auth Modal global display
window.openAuthGuard = function () {
    // This method now shows an elegant system modal for AuthGuard feedback modal      
    const modalRoot = document.createElement('div');  
    const page = document.documentElement;  
    const prevScrollY = window.scrollY; 
    document.body.prepend(modalRoot);          
    modalRoot.id = '__auth_guard_root';      
    modalRoot.className = `fixed inset-0 z-max bg-z0/50 backdrop-blur transition-opacity duration-300 opacity-100 hidden`; 
    // quick changes now      
    const onOusideClick = e => {         
        // for close via click      
        if (e.target?.classList?.contains('__auth_guard_root')) {     
            modalRoot.addEventListener('outside:close', closeModal);     
        }         
    };  
    const onEscapeClose = (e) => {          
        if (`${e.key}` === 'Escape') {           
            closeModal();       
        }     
    };     
    const closeModal = () => {           
        const container = modalRoot.querySelector('#auth-guard-content');           
        if (container) {       
                container.style.transform = 'scale(0.97)';          
                container.style.opacity = '0.85';        
                container.style.top = '-50px';           
            }          
        const fadeout = modalRoot;         
        fadeout.style.opacity = '0';          
        fadeout.addEventListener('transitionend', () => {              
            if (fadeout.parentNode === document.body) {                   
                document.body.removeChild(modalRoot);                   
                document.removeEventListener('keydown', onEscapeClose);          
                document.documentElement.classList.remove('AuthGuard.Lock');         
            }          
        });   
    };
           
    modalRoot.addEventListener('click', onOusideClick);         
    modalRoot.addEventListener('keydown', onEscapeClose);           
    // DS modal just write      
    modalRoot.setAttribute('hidden', '');          
    modalRoot.removeAttribute('hidden');   
    document.documentElement.classList.add('AuthGuard.Lock');

    modalRoot.insertAdjacentHTML('afterbegin',
		'<div class="fixed inset-0 z-max bg-z0/50 flex items-center justify-center p-4" id="_bg">'
			+'<div class="w-full max-w-lg rounded-2xl bg-white p-6 pb-10 px-8.chkp voided" id="auth-guard-content" style="transform: scale(0.9); opacity: 0%; transition-property: transform,opacity,top; transition-duration:300ms; animation: slideUp 330ms ease;">'
			+'<div class="relative">'
				+'<div class="absolute -top-10 -right-6 inline-block flex-shrink-0">'
				+'<button class="extlink__more simple__x" onclick="document.getElementById(\'__auth_guard_root\').remove()">✖ <span class="sr-only">Close</span></button>'
				+'</div>'
			+'   <div style="  background: linear-gradient(180deg, #060 360, rgb(57 65 245 / 0.8)); min-height: 67.5vh; min-height: max-content; overflow: hidden;"> '
			+'   <div class="rounded-2xl to- p-4 relative block">'
			+'	<div class="relative block  mx-auto my-4 ">'
			+'	  <div class="mx-auto flex     mb-0  rounded-full h-20 w-20 bg-white/30 items-center justify-center backdrop-blur-sm" > '
			+'	     <svg width="44" height="44" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>     '
			+'	  </div> '
			+'	</div>'
			+'	   <div class="text-center text-white space-y-80  mx-0 " style="margin: 42px 0; ">'
				+' <h2 class="text-3xl sm:text-4xl  font-semibold text-white  		flex items-center justify-center space-x-1 "><svg class=" h-7 w-7 stroke-[3] text-white">
						<path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /</svg><span>Keep Your Favorites</span></h2>'
				+'	    <p class="text-lg text-white/80">Sign up and get recommends just for you</p>'
			+'	   </div>'
			+'<div class="bg-white/90 backdrop-blur-sm rounded-lg p-14 pb-4 ">'
			+'<div class="space-y-0">'
			+'<button style="text-align: center; font-style: bold; background: linear-gradient(rgb(86 76 144), rgb(99 105 255))" class="w-full mb-6   inline-flex items-center justify-center px-4 py-4 text-base font-bold text-white rounded-xl transition-all hover:scale-105"> Continue with Google<img style="width: 28px; margin-left: 8px; height: 25px" src="https://static.mygg-assets.com/i/svg/myescape-hit.svg" alt="" > </button>'
			+' <button id="auth-switch-to-email" onclick="this.parentNode.querySelector(\'#emailSignupForm\').classList.remove(\'hidden\'); this.setAttribute(\'hidden\', \'\')" class="w-full py-3 px-5 text-purple-900/80 text-base font-bold rounded-xl border border-solid border-gray-100 bg-white hover:bg-gray-100 ">Not keen on social? Use Email</button>'
			+' <form  autocomplete="off" data-no-js="true">'
			+'<div id="emailSignupForm" class="hidden flex flex-col items-stretch mx-auto">  <input id="ClientEmailInput" style="border: 1px solid currentColor; padding: 1rem; " value="" type="email" name="email" class="mt-1 w-full  bg-slate-53 px-4 inline-block rounded-xl " placeholder="your@email.com" required />'
			+'  <input name="name" style="border: 1px solid currentColor; padding: 1rem;" type="text" class=" w-full px-4 inline-block mt-1 rounded-xl " value="" placeholder="your name" />'
			+'  <button style="padding-top: 17px; padding-bottom: 17px; border-radius: 10px; margin-top: 22px; text-size-adjust: none; font-family: [[ useFont ]]px" type="submit" data-gtmTrigger="click" name="action" value="ProceedCreate" class="w-full relative py-3  bg-[rgb(86,76,144)] bg-[rgb(86,76,144)] uppercase text-white rounded-xl">Get Started<script>document.currentScript?.parentNode?.dispatchEvent?.('a a auth: submit button submit')</script>' in> </button>'
			+'</form>'
			+'<p class="mt-3 text-[rgb(66,70,76)] text-center text-[0.95]">Already have an account? <a class="-underline -w-[max-content] text-pink-400 decoration-sky-400 text-lg underline font-bold inline-block">Login here</a> </p>'
		);      
    // // magic close    
    modalRoot.addEventListener('click', onOutClick (e) => {        
            const listenerParams = [e.target.nextSibling?.nextSibling ? e.target : e.target.parentNode].concat(arguments);      
            console.assert(false, new Error(` authGuard Modal closing logic event parameter ${listenerParams[0]}`));   
    });
};         
           
// preferred blend helper          
if (!window.ophileDialogHelpers) window.ophileDialogHelpers = {};       
window.ophileDialogHelpers.modalInnitAuth = openAuthGuard;
// Auth truncation helpers    
markContentBuilder();      
</script>;