Halo {!! $user->name !!} 👋🏻,
Selamat Datang di SIGaP (Sistem Informasi Gabungan Pelaporan) PT. SIAP.
Senang sekali saya bisa menyambut Anda.

Berikut ini adalah detail login anda:
*User Name:* {{ $user->email }}
*Password:* {{ $plain_password }}

Mohon untuk tidak membagikan detil login Anda kepada siapapun.
Apabila Anda kesulitan untuk mengingat password di atas, Anda bisa melalukan "Reset Password" melalui: {{ route('password.request') }}.

Anda dapat mengakses SIGaP melalui: {{ route('login') }}.

Terima Kasih 🙏,

Sunny ☀️
_NB: Ini adalah pesan yang dikirim oleh sistem, mohon untuk *tidak* membalas pesan ini._