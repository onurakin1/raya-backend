<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sip;
use App\Models\SipDevices;
use App\Models\SipUsers;
use App\Models\SipButtons;
use App\Enums\TokenAbility;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use phpseclib3\Net\SSH2;
use Illuminate\Support\Facades\Http;


class AuthController extends Controller
{
    public  function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|max:255',
            'password' => 'required|min:6'
        ]);

        $user = User::create($data);

        $sipUsers = SipUsers::create([
            'extension' => $request->email,
            'name' => $request->name,
            'voicemail' => 'novm',
            'ringtimer' => 0,
        ]);

        $sipPhones = SipButtons::create([
            'context_id' => 0,
            'exclude' => 0,
            'sortorder' => 0,
            'type' => 'extension',
            'device' => 'PJSIP/' . $request->email,
            'privacy' => null,
            'label' => $request->name,
            'group' => '',
            'exten' => $request->email,
            'email' => '',
            'context' => 'from-internal',
            'mailbox' => '',
            'channel' => '',
            'queuechannel' => 'Local/' . $request->email . '@from-queue/n|Penalty=0|MemberName=' . $request->name . '|StateInterface=PJSIP/' . $request->email,
            'extenvoicemail' => '',
            'queuecontext' => 'from-queue',
            'server' => '',
            'cssclass' => '',
            'autoanswerheader' => '__SIPADDHEADER51=Call-Info: answer-after=0.001',
            'sip_username' => $request->email,
            'sip_password' => $request->password
        ]);

        $sipDevices = SipDevices::create([
            'id' => $request->email,
            'tech' => 'pjsip',
            'dial' => 'PJSIP/' . $request->email,
            'devicetype' => 'fixed',
            'user' => $request->email,
            'description' => $request->name
        ]);
        $createdSip = [];

        $sipData = [
            ['keyword' => 'account', 'data' => $request->email, 'flags' => 39],
            ['keyword' => 'accountcode', 'data' => '', 'flags' => 15],
            ['keyword' => 'allow', 'data' => '', 'flags' => 13],
            ['keyword' => 'allow_subscribe', 'data' => 'yes', 'flags' => 29],
            ['keyword' => 'authenticate_qualify', 'data' => 'no', 'flags' => 35],
            ['keyword' => 'callerid', 'data' => 'device <' . $request->email . '>', 'flags' => 40],
            ['keyword' => 'callgroup', 'data' => '', 'flags' => 10],
            ['keyword' => 'context', 'data' => 'from-internal', 'flags' => 4],
            ['keyword' => 'deny', 'data' => '0.0.0.0/0.0.0.0', 'flags' => 17],
            ['keyword' => 'dial', 'data' => 'PJSIP/' . $request->email, 'flags' => 14],
            ['keyword' => 'direct_media', 'data' => 'no', 'flags' => 36],
            ['keyword' => 'disallow', 'data' => '', 'flags' => 12],
            ['keyword' => 'dtls_ca_file', 'data' => '', 'flags' => 23],
            ['keyword' => 'dtls_cert_file', 'data' => '', 'flags' => 21],
            ['keyword' => 'dtls_private_key', 'data' => '', 'flags' => 22],
            ['keyword' => 'dtls_setup', 'data' => 'actpass', 'flags' => 24],
            ['keyword' => 'dtls_verify', 'data' => 'no', 'flags' => 25],
            ['keyword' => 'dtmfmode', 'data' => 'rfc2833', 'flags' => 3],
            ['keyword' => 'ice_support', 'data' => 'no', 'flags' => 19],
            ['keyword' => 'mailbox', 'data' => $request->email, 'flags' => 16],
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
            ['keyword' => 'secret', 'data' => $request->password, 'flags' => 2],
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
                'id' => $request->email,      // Kullanıcının email'i
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

        $username = $request->email;
        $password = $request->password;

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

        return [
            'SipPhones' => $sipPhones,
            'SipUsers' => $sipUsers,
            'SipDevices' =>  $sipDevices,
            'Sip' => $createdSip
        ];
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
        $data = $request->validate([

            'email' => ['required', 'email', 'exists:users'],
            'password' => ['required', 'min:6']
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response([
                'message' => 'Bad creds'
            ], 401);
        }
        $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

        // $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $accessToken->plainTextToken,
            'refresh_token' => $refreshToken->plainTextToken
        ];
    }
    public function refreshToken(Request $request)
    {
        $accessToken = $request->user()->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        return response(['message' => "Token généré", 'token' => $accessToken->plainTextToken]);
    }
}
