<?php

namespace App\Model;

use Auth, Validator;

use ZipArchive;

use App\Apis\JarvisLogistics;
use App\Apis\ReceitaWS;
use App\Dao\BackupDao;
use App\Events\Support\LicenseCreatedEvent;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use \PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


use Carbon\Carbon;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class License
 * @package App\Model
 */
class License extends Model {

    use Notifiable, SoftDeletes;

	/**
     * The table name in database
     *
     * @var String
     */
	protected $table = 'licenca';

	/**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'cnpj';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * @var bool
     */
    public $incrementing = false;

    /**
     * Model events
     * @return void
     */
    protected static function boot() {

        parent::boot();

        static::creating(function($license) {

            $license->api_token = $license->api_token ?: strtolower(Str::random(16));

        });

        static::created(function($license) {

            event(new LicenseCreatedEvent($license));

        });

        static::updating(function($license) {

            $license->registerLog();

        });

    }

    /**
     * array actives status
     */
    const ACTIVE_STATUS = ['ATIVA', 'PENDENTE'];

    /**
     * @var array
     */
    public $errors;

    /**
     * Description
     * @return array
     */
    public function rules() {

        $rules = [
            'cnpj' => 'required|size:18',
            'inscricao_estadual' => 'max:50',
            'sistema' => 'required|max:50',
            'situacao' => 'required:max:20',
            'empresa' => 'required|min:10|max:255',
            'email' => 'nullable|email|max:255',
            'contato' => 'max:50',
            'telefone' => 'max:15',
            'nomeservidor' => 'max:50',
            'ip_interno_servidor' => 'max:50',
            'ip_externo_servidor' => 'max:50',
            'id_td' => 'max:50',
            'LicencaCobreBem1' => 'max:100',
            'LicencaCobreBem2' => 'max:100',
            'bairro' => 'max:50',
            'cidade' => 'max:50',
            'endereco' => 'max:100',
            'estado' => 'size:2',
            'nomeFantasia' => 'max:100',
            'numero' => 'max:10',
            'mobile_phone' => 'max:20'
        ];

        return $rules;

    }

    public function cnpj($onlyNumbers = false) {

        return !$onlyNumbers ? $this->cnpj : preg_replace('/[^0-9]/', '', $this->cnpj);

    }

	/**
	 * Tickets of this Licence
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function tickets() {

		return $this->hasMany(Ticket::class, 'cnpj');

	}

    /**
     * Incomes of this License
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function billing() {

        return $this->hasMany(Income::class, 'cnpj')->where('ind_cancelado', 0);

    }

    /**
     * Notifications of this License
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function notifications() {

        return Notification::where('public', true)->
                             orWhere('sistema', $this->sistema)->
                             orWhere('cnpj', $this->cnpj);

    }

    /**
     * Description
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notificationReadings() {

        return $this->hasMany(NotificationReading::class, 'cnpj');

    }

    /**
     * Description
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function system() {

        return $this->belongsTo(System::class, 'sistema', 'sistema');

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bills() {

        return $this->hasMany(LicenseBill::class, 'cnpj', 'cnpj');

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logErrors() {

        return $this->hasMany(LogError::class, 'cnpj', 'cnpj');

    }


    /* methods */

    /**
     * @param $query
     * @param int $days
     * @param bool $ignoreBlocked
     * @return mixed
     */
    public static function scopeWithOldBackup($query, $days = 3, $ignoreBlocked = true) {

        return $query->where(function($query) use($days) {
            return $query->whereRaw("datediff(now(), UltimoBackup) > {$days}")
                ->orWhereNull('UltimoBackup')
                ->orWhereRaw("datediff(now(), UltimoBackupValidado) > {$days}")
                ->orWhereNull('UltimoBackupValidado');
        })
        ->whereNotIn('cnpj', \App\Dao\BackupDao::whiteList())
        ->where('nfe_agro', 0)
        ->when($ignoreBlocked, function($query) {
            return $query->where('situacao', '<>', 'BLOQUEADA');
        });

    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeTokenMissing($query) {

        return $query->whereNull('api_token');

    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeActive($query) {

        return $query->whereIn('situacao', ['ATIVA', 'PENDENTE']);

    }

    /**
     * Description
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logBackups() {

        return $this->hasMany(LogBackup::class, 'cnpj', 'cnpj');
    }

    /**
     * @return null|static
     */
    public function lastBackup() {

        if ($this->UltimoBackup) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $this->UltimoBackup);
        }

        return null;

    }

    /**
     * Description
     * @return null|Carbon\Carbon
     */
    public function since() {

        if ($this->primeiroAcesso) {
            return Carbon::createFromFormat('Y-m-d', $this->primeiroAcesso);
        }

        return null;

    }

    /**
     * Description
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contacts() {

        return $this->hasMany(LicenseContact::class, 'cnpj');

    }

    /**
 * Description
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
    public function accounts() {

        return $this->hasMany(LicenseAccount::class, 'cnpj');

    }

    /**
     * Description
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userAccounts() {

        return $this->hasMany(UserLicense::class, 'cnpj');

    }

    /**
     * return list of logs
     * @return array App\Model\LicenseLog
     */
    public function logs() {

        return $this->belongsToMany(SupportUser::class, 'licenca_log', 'cnpj', 'codigousuario')->
                      withTimestamps()->
                      withPivot(['before', 'after'])->
                      latest('pivot_updated_at');

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function modules() {

        return $this->hasMany(LicenseModule::class, 'cnpj');

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function licencaBackupWhitelist() {

        return $this->hasOne(LicenseBackupWhiteList::class, 'cnpj');

    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function licenseFiles() {

        return $this->hasMany(LicenseFile::class, 'cnpj', 'cnpj');

    }

    /**
     * register log of diff in pivot
     * @param integer|null $userId
     * @param array|null $diff
     * @return void
     */
    public function registerLog($userId = null, $diff = null) {

        $userId = $userId ?: Auth::guard('support')->id();
        $diff = $diff ?: $this->getDiff();

        return $this->logs()->attach($userId, $diff);

    }

    /**
     * return the diff between new and old model data
     * @return array
     */
    protected function getDiff() {

        $after = $this->getDirty();
        $before = json_encode(array_intersect_key($this->fresh()->toArray(), $after));
        $after = json_encode($after);

        return compact('after', 'before');

    }

    /**
     * @param array $license
     * @param array|null $situations
     * @return \Illuminate\Database\Eloquent\Collection|Model[]
     */
    public static function get($license, $system, array $situations = null, $inactives = false) {

        $query =

        self::when($license, function($query) use ($license) {
            return $query->where('empresa', 'like', "%{$license}%")
                         ->orWhere('nomeFantasia', 'like', "%{$license}%")
                         ->orWhere('cnpj', 'like', "%{$license}%");
        })->
        when($system, function($query) use ($system) {
            return $query->where('sistema', 'like', "%{$system}%");
        })->
        when($situations, function($query) use ($situations) {
            return $query->whereIn('situacao', $situations);
        })->
        when($inactives, function($query) use ($situations) {
            return $situations ? $query->withTrashed() : $query->onlyTrashed();
        });

        return $query;

    }

    /**
     * Store (update/insert) model
     * @param array $data
     * @return this
     */
    public function store(array $data) {

        // validate request
        $v = Validator::make($data, $this->rules());

        // check for failure
        if ($v->fails()) {
            // set errors and return false
            $this->errors = $v->errors()->toArray();
            return false;
        }

        $this->cnpj =                @$data['cnpj'];
        $this->inscricao_estadual =  @$data['inscricao_estadual'] ?: @$data['ie'];
        $this->inscricao_municipal = @$data['inscricao_municipal'] ?: @$data['im'];
        $this->sistema =             @$data['sistema'];
        $this->situacao =            @$data['situacao'];
        $this->empresa =             @$data['empresa'];
        $this->email =               @$data['email'];
        $this->contato =             @$data['contato'];
        $this->telefone =            @$data['telefone'];
        $this->nomeservidor =        @$data['nomeservidor'];
        $this->ip_interno_servidor = @$data['ip_interno_servidor'];
        $this->ip_externo_servidor = @$data['ip_externo_servidor'];
        $this->id_tv =               @$data['id_tv'];
        $this->LicencaCobreBem1 =    @$data['LicencaCobreBem1'];
        $this->LicencaCobreBem2 =    @$data['LicencaCobreBem2'];
        $this->bairro =              @$data['bairro'];
        $this->cidade =              @$data['cidade'];
        $this->endereco =            @$data['endereco'];
        $this->estado =              @$data['estado'];
        $this->cep =                 @$data['cep'];
        $this->nomeFantasia =        @$data['nomeFantasia'];
        $this->numero =              @$data['numero'];
        $this->mobile_phone =        @$data['mobile_phone'];
        $this->cnae =                @$data['cnae'];
        $this->ibge =                @$data['ibge'];
        $this->nfe_agro =            @$data['nfe_agro'] ?: '0';

        $this->save();

        // Backup
        if (@$data['ignore_backup']) {
            $this->ignoreBackup($data['ignore_backup'] == 'on');
        }

        // Modules
        if (isset($data['modulos']) && is_array($data['modulos'])) {
            $this->storeModules($data['modulos']);
        }

        // Contacts
        if (isset($data['contacts']) && is_array($data['contacts'])) {
            $this->storeContacts($data['contacts']);
        }

        return $this;

    }

    /**
     * Associate an App\Model\LicenseAccount to this App\Model\License
     * @param array $data
     * @return void
     */
    public function storeAccount(array $data) {

        $account = new LicenseAccount;
        $account->description =     @$data['description'];
        $account->account_type_id = @$data['account_type_id'];
        $account->account =         @$data['account'];
        $account->password =        @$data['password'];

        $this->accounts()->save($account);

    }

    /**
     * @param array $modules
     * @return $this
     */
    public function storeModules(array $modules) {

        foreach($modules as $module_name) {

            if (!$this->modules()->where('codigoprograma', $module_name)->exists()) {

                $module = new LicenseModule();
                $module->codigoprograma = $module_name;
                $this->modules()->save($module);

            }

        }

        return $this;

    }

    /**
     * Associate an App\Model\LicenseContact to this App\Model\License
     * @param array $data
     * @return void
     */
    public function storeContact(array $data) {

        $contact = new LicenseContact;
        $contact->description =     @$data['description'] ?? 'Outros';
        $contact->contact_type_id = @$data['contact_type_id'];
        $contact->value =           @$data['value'];

        $this->contacts()->save($contact);

    }

    /**
     * @param array $contacts_data
     */
    public function storeContacts(array $contacts_data) {

        foreach ($contacts_data as $data) {

            if (!isset($data['contact_type_id']) && isset($data['contact_type_description'])) {
                $contactType = ContactMethod::where('descricao', $data['contact_type_description'])->first();
                $data['contact_type_id'] = $contactType ? $contactType->idformaContato : null;
            }

            if (!$this->contacts()->where('contact_type_id', $data['contact_type_id'])
                    ->where('value', $data['value'])
                    ->exists()) {

                $this->storeContact($data);

            }

        }

    }

    /**
     * @param array $data
     * @return mixed
     */
    public function storeFile(array $data) {

        $date = \Carbon\Carbon::now()->format('YmdHis');

        $ext = $data['file']->clientExtension();
        $name = "{$data['name']}_{$date}";
        $path = "{$name}.{$ext}";

        $file = File::store($data['file'], $name, "uploads/licenses/{$this->cnpj(true)}", $path);

        $this->licenseFiles()->save(new LicenseFile([
            'cnpj' => $this->cnpj,
            'file_id' => $file->id,
            'codigousuario' => Auth::guard('support')->user()->codigousuario
        ]));

        return true;

    }

    /**
     * Description
     * @return type
     */
    public static function onlyActives() {

        return self::whereIn('situacao', self::ACTIVE_STATUS);

    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeInDebt($query) {

        return $query->where('balance', '<', 0);

    }

    /**
     * @return bool
     */
    public function hasBackupPendencies() {

        if (in_array($this->cnpj, BackupDao::whiteList()))
            return false;

        if (!$this->UltimoBackup || !$this->UltimoBackupValidado)
            return true;

        $now = Carbon::now();

        // UltimoBackup
        if ($this->UltimoBackup) {
            $dtUltimoBackup = Carbon::createFromFormat('Y-m-d H:i:s', $this->UltimoBackup);

            if ($dtUltimoBackup->diffInDays($now) >= 3)
                return true;
        }

        // UltimoBackupValidado
        if ($this->UltimoBackupValidado) {
            $dtUltimoBackupValidado = Carbon::createFromFormat('Y-m-d H:i:s', $this->UltimoBackupValidado);

            if ($dtUltimoBackupValidado->diffInDays($now) >= 3)
                return true;
        }

        return false;

    }

    /**
     * @param $cnpj
     * @param $ie
     * @param $sistema
     * @return License|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function createFromCNPJ($cnpj, $ie, $sistema) {

        $license = License::where('cnpj', $cnpj)->withTrashed()->first();
        if ($license) return $license;

        $cnpjData = ReceitaWS::getData($cnpj);
        if (!$cnpjData || $cnpjData->status == "ERROR") return null;

        // store Licença
        $license = new License();
        $license->cnpj = $cnpj;
        $license->inscricao_estadual = $ie;
        $license->empresa = $cnpjData->nome;
        $license->nomeFantasia = $cnpjData->fantasia;
        $license->sistema = $sistema;
        $license->situacao = 'ATIVA';
        $license->telefone = $cnpjData->telefone;
        $license->email = $cnpjData->email;
        $license->cnae = @$cnpjData->atividade_principal[0]->code;

        $cepInfo = @json_decode(JarvisLogistics::getCep($cnpjData->cep))->data;

        $license->cidade = $cnpjData->municipio;
        $license->ibge = @$cepInfo->ibge;
        $license->cep = $cnpjData->cep;
        $license->endereco = $cnpjData->logradouro;
        $license->numero = $cnpjData->numero;
        $license->bairro = $cnpjData->bairro;
        $license->estado = $cnpjData->uf;
        $license->save();

        return $license;

    }

    /**
     * @param bool $ignoreBackup
     * @return bool
     */
    public function ignoreBackup($ignoreBackup = true) {

        $this->licencaBackupWhitelist()->delete();

        if ($ignoreBackup) {
            $this->licencaBackupWhitelist()->save(new LicenseBackupWhiteList([
                'cnpj' => $this->cnpj])
            );
        }

        return true;

    }

    /**
     * @return array|false|string[]
     */
    public function emailsFiscais() {

        $emails = $this->emails_fiscal ? explode(';', $this->emails_fiscal) : [];
        $emails[] = $this->email;

        return $emails;

    }

    /**
     * @param ZipArchive $zipFile
     * @param $license
     * @param $nfes
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public static function exportExcel($items) {

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()
                    ->getStyle("A")
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_TEXT);
        $sheet =  $spreadsheet;
        $sheet->setCellValue('A1', 'CNPJ');
        $sheet->setCellValue('B1', 'Inscrição Municipal');
        $sheet->setCellValue('C1', 'Inscrição Estadual');
        $sheet->setCellValue('D1', 'Sistema');
        $sheet->setCellValue('E1', 'Situação');
        $sheet->setCellValue('F1', 'Empresa');
        $sheet->setCellValue('G1', 'Nome Fantasia');
        $sheet->setCellValue('H1', 'Email');
        $sheet->setCellValue('I1', 'Emails Fiscal');
        $sheet->setCellValue('J1', 'Contato');
        $sheet->setCellValue('K1', 'Telefone');
        $sheet->setCellValue('L1', 'Celular');
        $sheet->setCellValue('M1', 'Nome do Servidor');
        $sheet->setCellValue('N1', 'Ip Interno do Servidor');
        $sheet->setCellValue('O1', 'Ip Externa do Servidor');
        $sheet->setCellValue('P1', 'Primeiro Acesso');
        $sheet->setCellValue('Q1', 'Ultimo Backup');
        $sheet->setCellValue('R1', 'Ultimo Backup Validado');
        $sheet->setCellValue('S1', 'Endereço');
        $sheet->setCellValue('T1', 'Bairro');
        $sheet->setCellValue('U1', 'Cidade');
        $sheet->setCellValue('V1', 'Estado');
        $sheet->setCellValue('W1', 'Numero');
        $sheet->setCellValue('X1', 'CPE');
        $sheet->setCellValue('Y1', 'IBGE');
        $sheet->setCellValue('Z1', 'CNAE');
        $i = 3;

        foreach ($items as $value) {

            $sheet->setCellValue("A{$i}", $value->cnpj);

            $sheet->getActiveSheet()
                  ->getStyle("A")
                  ->getNumberFormat()
                  ->setFormatCode(NumberFormat::FORMAT_TEXT);

            $sheet->setCellValue("B{$i}", "$value->inscricao_municipal");
            $sheet->setCellValue("C{$i}", "$value->inscricao_estadual");
            $sheet->setCellValue("D{$i}", "$value->sistema");
            $sheet->setCellValue("E{$i}", "$value->situacao");
            $sheet->setCellValue("F{$i}", "$value->empresa");
            $sheet->setCellValue("G{$i}", "$value->nomeFantasia");
            $sheet->setCellValue("H{$i}", "$value->email");
            $sheet->setCellValue("I{$i}", "$value->emails_fiscal");
            $sheet->setCellValue("J{$i}", "$value->contato");
            $sheet->setCellValue("K{$i}", "$value->telefone");
            $sheet->setCellValue("L{$i}", "$value->mobile_phone");
            $sheet->setCellValue("M{$i}", "$value->nomeservidor");
            $sheet->setCellValue("N{$i}", "$value->ip_interno_servidor");
            $sheet->setCellValue("O{$i}", "$value->ip_externo_servidor");
            $sheet->setCellValue("P{$i}", "$value->primeiroAcesso");
            $sheet->setCellValue("Q{$i}", "$value->UltimoBackup");
            $sheet->setCellValue("R{$i}", "$value->UltimoBackupValidado");
            $sheet->setCellValue("S{$i}", "'$value->endereco'");
            $sheet->setCellValue("T{$i}", "'$value->bairro'");
            $sheet->setCellValue("U{$i}", "'$value->cidade'");
            $sheet->setCellValue("V{$i}", "'$value->estado'");
            $sheet->setCellValue("W{$i}", "$value->numero");
            $sheet->setCellValue("X{$i}", "$value->cep");
            $sheet->setCellValue("Y{$i}", "$value->ibge");
            $sheet->setCellValue("Z{$i}", "$value->cnae");
            $i++;

        }

        // save & close
        $writer = new Xlsx($spreadsheet);
        $writer->save('Relatorio_licenca.xlsx');

    }
}
