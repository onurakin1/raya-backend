<?php

namespace App\Http\Controllers;

use App\Models\MeetMe;
use App\Models\Rooms;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\SipDevices;
use App\Models\SipUsers;
use App\Models\SipButtons;
use App\Models\Sip;
use App\Models\RoomUsers;
use App\Models\User;
use App\Models\TourDetails;
use phpseclib3\Net\SSH2;

class ChannelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return MeetMe::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function CreateChannel(Request $request)
    {
        try {
            $user = $request->user();
            $userId = $user->id;
    
            // Fonksiyon: Oda kodu oluşturma
            function generateCode()
            {
                $time = time() / 15;
                $code = substr(md5($time), 0, 6);
                $numericCode = substr(str_pad(preg_replace('/\D/', '', $code), 6, '0', STR_PAD_LEFT), 0, 6);
                return $numericCode;
            }
    
            // 24 saatlik geçerlilik süresi hesaplama
            $now = Carbon::now();
            $expirationTime = $now->copy()->setTime(23, 59, 59);
    
            // Kanal oluşturma
            $createChannel = MeetMe::create([
                'exten' => $request->number,
                'options' => 'qMm',
                'userpin' => "",
                'adminpin' => $request->password,
                'description' => $request->name,
                'joinmsg_id' => 0,
                'music' => 'inherit',
                'users' => 0,
                'expiration_time' => $expirationTime // Geçerlilik süresi ekleniyor
            ]);
    
            // Oda oluşturma
            $roomCode = generateCode();
            $today = Carbon::today();
            $createRoom = Rooms::create([
                'name' => $request->number,
                'room_code' => $roomCode,
                'updated_at' => $today,
                'created_by' => $userId,
                'tour_id' => $request->tour_id,
                'expiration_time' => $expirationTime // Geçerlilik süresi ekleniyor
            ]);
    
            // Tur bilgilerini güncelleme
            $tour = TourDetails::where('tour_id', $request->tour_id)->first();
            $user = User::where('id', $userId)->first();
    
            if ($tour) {
                // Mevcut voice_rooms verisini kontrol et
                $existingRooms = json_decode($tour->voice_rooms, true) ?? [];
    
                // Yeni odayı mevcut voice_rooms array'ine ekle
                $newRoom = [
                    'id' => $createRoom->id,
                    'name' => $createRoom->name,
                    'expires_time' => $createRoom->expiration_time,
                    'user' => [
                        'name' => $user->email,
                        'photo_link' => $user->photo_link
                    ]
                ];
    
                // Mevcut voice_rooms array'ine yeni odayı ekle
                $existingRooms[] = $newRoom;
    
                // Güncellenmiş voice_rooms dizisini kaydet
                $tour->voice_rooms = json_encode($existingRooms);
                $tour->save();
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Channel created successfully!',
                'data' => $createRoom
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create channel: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    
    public function generateRoomCode(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;
        $roomId = $request->query('room_id');

        // Kod oluşturma fonksiyonu
        function refreshCode()
        {
            $time = time() / 15;
            $code = substr(md5($time), 0, 6);
            // Sadece rakamlardan oluşan bir kod oluştur
            $numericCode = substr(str_pad(preg_replace('/\D/', '', $code), 6, '0', STR_PAD_LEFT), 0, 6);
            return $numericCode;
        }

        // Odayı bulmaya çalış, first() null dönebilir
        $room = Rooms::where('id', $roomId)->where('created_by', $userId)->first();

        // Eğer oda bulunamazsa, 404 hatası gönder
        if (!$room) {
            return response()->json([
                'status' => false,
                'message' => 'No rooms found for this user.',
                'data' => null
            ], 404); // 404 Not Found
        }

        // Yeni room_code oluştur
        $newRoomCode = refreshCode();

        // Room kaydını güncelle
        $room->room_code = $newRoomCode;
        $room->save();

        // Başarılı yanıt gönder
        return response()->json([
            'status' => true,
            'message' => 'The room code has been successfully updated.',
            'data' => $room
        ]);
    }


    public function roomRegistration(Request $request)
    {
        $roomId = $request->query('room_id');
        $room = Rooms::where('id', $roomId)->first();

        // 4 haneli kullanıcı adı oluşturma (rastgele sayı)
        $username = str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);

        // Şifreyi oluşturacak karakterlerin tanımı
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';

        // 6 haneli rastgele şifre oluşturma
        $password = '';
        for ($i = 0; $i < 6; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }

        // Rastgele 5 haneli bir numara oluşturma (name için)
        $name = 'User-' . $username;

        $today = Carbon::today();
        $roomUsers = RoomUsers::create([
            'name' => $username,
            'password' => $password,
            'role' => 'User',
            'created_at' => $today,
            'is_active' => true
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

        // Örnek olarak username, password ve name'i döndürüyoruz
        return response()->json([
            'status' => true,
            'message' => 'User added to room successfully.',
            'data' => [
                'name' => $roomUsers->name,
                'role' => $roomUsers->role,
                'created_at' => $roomUsers->created_at,
                'is_active' => $roomUsers->is_active,
                'link' => "https://pbx.limonisthost.com/", // link ekleme
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MeetMe $spi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MeetMe $spi)
    {
        //
    }
}
