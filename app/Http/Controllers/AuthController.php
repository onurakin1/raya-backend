<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sip;
use App\Models\SipDevices;
use App\Models\SipUsers;
use App\Models\SipButtons;
use App\Enums\TokenAbility;
use App\Models\Company;
use App\Models\CompanyToGuide;
use App\Models\Rooms;
use App\Models\RoomUsers;
use App\Models\UserDevices;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use phpseclib3\Net\SSH2;
use Illuminate\Support\Facades\Http;


class AuthController extends Controller
{
    public  function register(Request $request)
    {
   
            $languageType = $request->header('Accept-Language');
            $data = $request->validate([
                'name' => 'required|max:255',
                'username' => 'required|max:255',
                'password' => 'required|min:6'
            ]);
            $today = Carbon::today();
            // 4 haneli kullanıcı adı oluşturma (rastgele sayı)
            $username = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
            $company = Company::where('name', $request->company)->first();

    

            // Şifreyi oluşturacak karakterlerin tanımı
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';

            // 6 haneli rastgele şifre oluşturma
            $password = '';
            for ($i = 0; $i < 6; $i++) {
                $password .= $characters[rand(0, strlen($characters) - 1)];
            }

            // Rastgele 5 haneli bir numara oluşturma (name için)
            $name = 'User-' . $username;

            $user = User::create([
                'name' => $request->name,
                'last_name' => $request->last_name,
                'phone_number' => $request->phone_number,
                'email' => $request->username,
                'photo_link' => $request->photo_link,
                'password' =>  $request->password
            ]);
            $companyToGuide = CompanyToGuide::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
       
            ]);
            $addAsteriskUsers = RoomUsers::create([
                'name' => $username,
                'password' => $password,
                'role' => 'Guide',
                'created_at' => $today,
                'is_active' => true,
                'user_id' => $user->id
            ]);

            $sipUsers = SipUsers::create([
                'extension' => $username,
                'name' => $name,
                'voicemail' => 'novm',
                'ringtimer' => 0,
            ]);

            $sipPhones = SipButtons::create([
                'context_id' => 0,
                'exclude' => 0,
                'sortorder' => 0,
                'type' => 'extension',
                'device' => 'PJSIP/' . $username,
                'privacy' => null,
                'label' => $name,
                'group' => '',
                'exten' => $username,
                'email' => '',
                'context' => 'from-internal',
                'mailbox' => '',
                'channel' => '',
                'queuechannel' => 'Local/' . $username . '@from-queue/n|Penalty=0|MemberName=' . $name . '|StateInterface=PJSIP/' . $username,
                'extenvoicemail' => '',
                'queuecontext' => 'from-queue',
                'server' => '',
                'cssclass' => '',
                'autoanswerheader' => '__SIPADDHEADER51=Call-Info: answer-after=0.001',
                'sip_username' => $username,
                'sip_password' => $request->password
            ]);

            $sipDevices = SipDevices::create([
                'id' => $username,
                'tech' => 'pjsip',
                'dial' => 'PJSIP/' . $username,
                'devicetype' => 'fixed',
                'user' => $username,
                'description' => $name
            ]);
            $createdSip = [];

            $sipData = [
                ['keyword' => 'account', 'data' => $username, 'flags' => 39],
                ['keyword' => 'accountcode', 'data' => '', 'flags' => 15],
                ['keyword' => 'allow', 'data' => '', 'flags' => 13],
                ['keyword' => 'allow_subscribe', 'data' => 'yes', 'flags' => 29],
                ['keyword' => 'authenticate_qualify', 'data' => 'no', 'flags' => 35],
                ['keyword' => 'callerid', 'data' => 'device <' . $username . '>', 'flags' => 40],
                ['keyword' => 'callgroup', 'data' => '', 'flags' => 10],
                ['keyword' => 'context', 'data' => 'from-internal', 'flags' => 4],
                ['keyword' => 'deny', 'data' => '0.0.0.0/0.0.0.0', 'flags' => 17],
                ['keyword' => 'dial', 'data' => 'PJSIP/' . $username, 'flags' => 14],
                ['keyword' => 'direct_media', 'data' => 'no', 'flags' => 36],
                ['keyword' => 'disallow', 'data' => '', 'flags' => 12],
                ['keyword' => 'dtls_ca_file', 'data' => '', 'flags' => 23],
                ['keyword' => 'dtls_cert_file', 'data' => '', 'flags' => 21],
                ['keyword' => 'dtls_private_key', 'data' => '', 'flags' => 22],
                ['keyword' => 'dtls_setup', 'data' => 'actpass', 'flags' => 24],
                ['keyword' => 'dtls_verify', 'data' => 'no', 'flags' => 25],
                ['keyword' => 'dtmfmode', 'data' => 'rfc2833', 'flags' => 3],
                ['keyword' => 'ice_support', 'data' => 'no', 'flags' => 19],
                ['keyword' => 'mailbox', 'data' => $username, 'flags' => 16],
                ['keyword' => 'max_contacts', 'data' => 1, 'flags' => 32],
                ['keyword' => 'media_encryption', 'data' => 'no', 'flags' => 26],
                ['keyword' => 'media_use_received_transport', 'data' => 'no', 'flags' => 37],
                ['keyword' => 'message_context', 'data' => '', 'flags' => 27],
                ['keyword' => 'nat', 'data' => 'no', 'flags' => 31],
                ['keyword' => 'outbound_proxy', 'data' => '', 'flags' => 38],
                ['keyword' => 'permit', 'data' => '0.0.0.0/0.0.0.0', 'flags' => 18],
                ['keyword' => 'pickupgroup', 'data' => '', 'flags' => 11],
                ['keyword' => 'qualifyfreq', 'data' => 60, 'flags' => 7],
                ['keyword' => 'qualify_timeout', 'data' => 3.0, 'flags' => 34],
                ['keyword' => 'remove_existing', 'data' => 'no', 'flags' => 33],
                ['keyword' => 'rtcp_mux', 'data' => 'no', 'flags' => 9],
                ['keyword' => 'secret', 'data' => $password, 'flags' => 2],
                ['keyword' => 'sendrpid', 'data' => 'no', 'flags' => 6],
                ['keyword' => 'stir_shaken', 'data' => 'off', 'flags' => 30],
                ['keyword' => 'subscribe_context', 'data' => '', 'flags' => 28],
                ['keyword' => 'transport', 'data' => 'transport-udp', 'flags' => 8],
                ['keyword' => 'trustrpid', 'data' => 'yes', 'flags' => 5],
                ['keyword' => 'use_avpf', 'data' => 'no', 'flags' => 20],
            ];

            // Veritabanına dinamik olarak ekleme işlemi
            foreach ($sipData as $data) {
                $sipDevice = Sip::create([
                    'id' => $username,      // Kullanıcının email'i
                    'keyword' => $data['keyword'],
                    'data' => $data['data'],
                    'flags' => $data['flags'],
                ]);

                $createdSip[] = $sipDevice;
            }

            $ssh = new SSH2('213.14.229.130'); // Asterisk sunucunuzun IP'sini yazın
            if (!$ssh->login('root', 'B0ncuk24')) {
                throw new \Exception('SSH bağlantısı başarısız.');
            }

            $command = "
        echo \"[$username]\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"type=endpoint\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"context=from-external\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"disallow=all\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"allow=ulaw,alaw\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"auth=auth$username\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"aors=$username\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"transport=transport-udp\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"direct_media=no\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"rtp_symmetric=yes\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"force_rport=yes\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"\" >> /etc/asterisk/pjsip_custom.conf; 
    
        # Authentication tanımı
        echo \"[auth$username]\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"type=auth\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"auth_type=userpass\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"username=$username\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"password=$password\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"\" >> /etc/asterisk/pjsip_custom.conf; 
    
        # AOR tanımı
        echo \"[$username]\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"type=aor\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"max_contacts=1\" >> /etc/asterisk/pjsip_custom.conf;
    
        # IP Filter tanımı
        echo \"[$username-identifier]\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"type=identify\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"endpoint=$username\" >> /etc/asterisk/pjsip_custom.conf; 
        echo \"match=213.14.229.130\" >> /etc/asterisk/pjsip_custom.conf; 
        ";

            $ssh->exec($command);
            $ssh->exec('asterisk -rx "pjsip reload"');


            $output = $ssh->exec('sudo asterisk -rx "dialplan reload');
            $output1 = $ssh->exec('sudo asterisk -rx "pjsip reload');
            if (!empty($output)) {
                throw new \Exception("Asterisk yeniden başlatma hatası: " . $output);
            }






            // $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
            // $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));
            // Erişim Token süresi ve oluşturulması
            $accessTokenExpiration = Carbon::now()->addMinutes(config('sanctum.ac_expiration'));
            $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], $accessTokenExpiration);

            // Yenileme Token süresi ve oluşturulması
            $refreshTokenExpiration = Carbon::now()->addMinutes(config('sanctum.rt_expiration'));
            $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], $refreshTokenExpiration);


            // 'expires_in' bilgileri (saniye cinsinden)
            $accessTokenExpiresIn = round(Carbon::now()->diffInSeconds($accessTokenExpiration) / 3600, 2);
            $refreshTokenExpiresIn = Carbon::now()->diffInSeconds($refreshTokenExpiration) / 3600;
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            return response()->json([
                'status' => true,
                'message' => $successMessage,
                'data' => [
                    'access_token' => $accessToken->plainTextToken,
                    'refresh_token' => $refreshToken->plainTextToken,
                    'expires_in' => $accessTokenExpiresIn,
                    'user' => [
                        'id' => $user->id,
                        'firstname' => $user->name,
                        'lastname' => $user->last_name,
                        'phone_number' =>  $user->phone_number,
                        'username' => $user->email,
                        'photo_link' => $user->photo_link,
                        'isabel' => [
                            'username' => $addAsteriskUsers->name,
                            'password' => $addAsteriskUsers->password,
                            'url' => "pbx.limonisthost.com"
                        ],
                    ],
                ],
            ], 200);
      
    }

    function disableEndpoint(Request $request)
    {
        $ssh = new SSH2('213.14.229.130');  // Asterisk sunucunuzun IP adresi
        if (!$ssh->login('root', 'B0ncuk24')) {  // SSH root bilgilerinizi kullanın
            throw new \Exception('SSH bağlantısı başarısız.');
        }

        // Endpoint ile ilgili ayarları bulup yorum satırına almak için komut
        $command = "
        sed -i '/\\[$request->endpoint\\]/,/^\\[/ { s/^/;/; }' /etc/asterisk/pjsip_custom.conf; 
        asterisk -rx \"pjsip reload\";  
    ";

        $ssh->exec($command);

        return "Endpoint $request->endpoint devre dışı bırakıldı.";
    }
    public  function login(Request $request)
    {
        
            $languageType = $request->header('Accept-Language');
            $user = User::where('email', $request->username)->first();
            $today = Carbon::today();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response([
                    'message' => 'Bad creds'
                ], 401);
            }
            $rooms = Rooms::where('created_by', $user->id)->first();
            $isabel_user = RoomUsers::where('user_id', $user->id)->first();

            $user_devices = UserDevices::create([
               
                'app_version' => $request->app_version,
                'device_id' => $request->device_id,
                'device_model' => $request->device_model,
                'device_version' => $request->device_version,
                'device_type' => $request->device_type,
                'user_id' => $user->id,
                'created_at' => $today,
                'updated_at'  => $today

            ]);


            $userId = $user->id;
            $companyToGuide = Company::whereHas('guides', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->first(); // Koleksiyonun ilk elemanını al
            
            if ($companyToGuide) {
                $company = [
                    'id' => $companyToGuide->id,
                    'name' => $companyToGuide->name,
                ];
            } else {
                $company = null; // Eğer hiç şirket yoksa null dönebilir
            }

            // $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
            // $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));


            $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->setTimezone('Europe/Istanbul')->addMinutes(config('sanctum.ac_expiration')));


            $accessTokenExpirationMinutes = config('sanctum.ac_expiration'); // Örneğin 60
            $accessTokenExpiration = Carbon::now()->setTimezone('Europe/Istanbul')->addMinutes($accessTokenExpirationMinutes);
            $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], $accessTokenExpiration);

            // Yenileme Token süresi ve oluşturulması
            // Yenileme Token süresi (24 saat = 86400 saniye)
            $refreshTokenExpirationMinutes = config('sanctum.rt_expiration'); // Örneğin 1440 (24 saat)
            $refreshTokenExpiration = Carbon::now()->addMinutes($refreshTokenExpirationMinutes);
            $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], $refreshTokenExpiration);


            $accessTokenExpiresIn = $accessTokenExpirationMinutes * 60; // 60 dakika = 3600 saniye
            $refreshTokenExpiresIn = $refreshTokenExpirationMinutes * 60; // 1440 dakika = 86400 saniye

            // $token = $user->createToken('auth_token')->plainTextToken;

            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            return response()->json([
                'status' => true,
                'message' => $successMessage,
                'data' => [
                    'access_token' => $accessToken->plainTextToken,
                    'refresh_token' => $refreshToken->plainTextToken,
                    'expires_in' => $accessTokenExpiresIn,
                    'user' => [
                        'id' => $user->id,
                        'firstname' => $user->name,
                        'lastname' => $user->last_name,
                        'phone_number' =>  $user->phone_number,
                        'username' => $user->email,
                        'photo_link' => $user->photo_link ?: "",
                        'isabel' => [
                            'username' => $isabel_user->username,
                            'password' => $isabel_user->password,
                            'url' => "pbx.limonisthost.com"
                        ],
                        'room' => $rooms,
                        'company' => $company
                    ],
                ],




            ], 200);
       
    }
    public function logout(Request $request)
    {
        try {
            $languageType = $request->header('Accept-Language');
            $request->user()->tokens()->delete();
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            return [
                'status' => true,
                'message' => $successMessage
            ];
        } catch (\Exception $e) {
            // Dil bilgisine göre hata mesajını ayarla
            $errorMessage = ($languageType === 'tr') ? 'Sunucu hatası oluştu' : 'Server error occurred';

            // Hata durumunda JSON yanıtı döndür
            return response()->json([
                'status' => false,
                'message' => $errorMessage, // Hata mesajı
            ], 500); // 500 sunucu hatası kodu
        }
    }
    public function refreshToken(Request $request)
    {
        try{
            $languageType = $request->header('Accept-Language');
            $accessToken = $request->user()->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            return response(['message' =>  $successMessage, 'token' => $accessToken->plainTextToken]);
        }
        catch (\Exception $e) {
            // Dil bilgisine göre hata mesajını ayarla
            $errorMessage = ($languageType === 'tr') ? 'Sunucu hatası oluştu' : 'Server error occurred';

            // Hata durumunda JSON yanıtı döndür
            return response()->json([
                'status' => false,
                'message' => $errorMessage, // Hata mesajı
            ], 500); // 500 sunucu hatası kodu
        }
     
    }
}
