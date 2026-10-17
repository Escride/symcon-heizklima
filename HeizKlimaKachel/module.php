<?php

declare(strict_types=1);

class HeizungKlimaKachel extends IPSModule
{
    private const TEXT_ELEMENTS = [
        'name'        => ['Name', 18],
        'season'      => ['Season', 13],
        'actualLabel' => ['ActualLabel', 14],
        'actualValue' => ['ActualValue', 14],
        'targetLabel' => ['TargetLabel', 14],
        'targetValue' => ['TargetValue', 14],
        'heatLabel'   => ['HeatLabel', 14],
        'heatValue'   => ['HeatValue', 14],
        'coolLabel'   => ['CoolLabel', 14],
        'coolValue'   => ['CoolValue', 14],
        'co2Label'    => ['CO2Label', 14],
        'co2Value'    => ['CO2Value', 14],
        'ventLabel'   => ['VentLabel', 14],
        'ventValue'   => ['VentValue', 14]
    ];

    // Schlüssel in der Kachel => Name der Eigenschaft (Variable)
    private const VARS = [
        'season'      => 'SeasonVar',
        'actual'      => 'ActualVar',
        'target'      => 'TargetVar',
        'heatPower'   => 'HeatPowerVar',
        'coolPower'   => 'CoolPowerVar',
        'heatRelease' => 'HeatReleaseVar',
        'coolRelease' => 'CoolReleaseVar',
        'co2'         => 'CO2Var',
        'ventActive'  => 'VentActiveVar',
        'ventPower'   => 'VentPowerVar'
    ];

    public function Create()
    {
        parent::Create();

        // Namen
        $this->RegisterPropertyString('Name', 'Wohnzimmer');
        $this->RegisterPropertyString('LabelActual', 'Ist');
        $this->RegisterPropertyString('LabelTarget', 'Soll');
        $this->RegisterPropertyString('LabelHeat', 'Heizung');
        $this->RegisterPropertyString('LabelCool', 'Klima');
        $this->RegisterPropertyString('LabelCO2', 'CO₂');
        $this->RegisterPropertyString('LabelVent', 'Lüftung');
        $this->RegisterPropertyString('NamePosition', 'top-left');

        foreach (self::TEXT_ELEMENTS as [$property, $fontSize]) {
            $this->RegisterPropertyInteger('Text' . $property . 'Color', -1);
            $this->RegisterPropertyString('Text' . $property . 'Font', '');
            $this->RegisterPropertyInteger('Text' . $property . 'Size', $fontSize);
            $this->RegisterPropertyInteger('Text' . $property . 'BackgroundColor', -1);
            $this->RegisterPropertyInteger('Text' . $property . 'BorderWidth', 0);
            $this->RegisterPropertyInteger('Text' . $property . 'BorderColor', -1);
            $this->RegisterPropertyInteger('Text' . $property . 'Image', 0);
            $this->RegisterPropertyInteger('Text' . $property . 'ImageOpacity', 100);
        }

        // Variablen
        foreach (self::VARS as $prop) {
            $this->RegisterPropertyInteger($prop, 0);
        }

        // Optionen
        $this->RegisterPropertyBoolean('SeasonInvert', false);
        $this->RegisterPropertyBoolean('HeatInvert', false);
        $this->RegisterPropertyBoolean('CoolInvert', false);
        $this->RegisterPropertyString('HeatReleasePos', 'right');
        $this->RegisterPropertyString('CoolReleasePos', 'right');

        // Bereiche
        $this->RegisterPropertyFloat('TempMin', 15.0);
        $this->RegisterPropertyFloat('TempMax', 30.0);
        $this->RegisterPropertyFloat('TempStep', 0.5);
        $this->RegisterPropertyInteger('CO2Min', 400);
        $this->RegisterPropertyInteger('CO2Max', 2000);

        // Farben (-1 = transparent / Standard)
        $this->RegisterPropertyInteger('ColorCold', hexdec('3B82F6'));
        $this->RegisterPropertyInteger('ColorWarm', hexdec('F97316'));
        $this->RegisterPropertyInteger('ColorVent', hexdec('14B8A6'));
        $this->RegisterPropertyInteger('TextColor', -1);

        // Hintergrund
        $this->RegisterPropertyInteger('BackgroundColor', -1);
        $this->RegisterPropertyInteger('BackgroundOpacity', 100);
        $this->RegisterPropertyInteger('BackgroundImage', 0);
        $this->RegisterPropertyString('ImagePosition', 'full');
        $this->RegisterPropertyInteger('ImageSize', 50);
        $this->RegisterPropertyInteger('ImageOpacity', 40);

        // Diese Instanz ist eine Kachel-Visualisierung
        $this->SetVisualizationType(1);
    }

    public function ApplyChanges()
    {
        parent::ApplyChanges();

        // Alte Nachrichtenregistrierungen entfernen
        foreach ($this->GetMessageList() as $senderID => $messages) {
            foreach ($messages as $message) {
                $this->UnregisterMessage($senderID, $message);
            }
        }

        // Neu registrieren
        foreach (self::VARS as $prop) {
            $id = $this->ReadPropertyInteger($prop);
            if ($id > 0 && IPS_VariableExists($id)) {
                $this->RegisterMessage($id, VM_UPDATE);
            }
        }

        $this->UpdateVisualizationValue(json_encode($this->GetFullMessage(), JSON_HEX_TAG));
    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        if ($Message === VM_UPDATE) {
            $this->UpdateVisualizationValue(json_encode(['values' => $this->GetValues()], JSON_HEX_TAG));
        }
    }

    // Wird von der Kachel aufgerufen (requestAction in module.html)
    public function RequestAction($Ident, $Value)
    {
        switch ($Ident) {
            case 'target':
                $min = $this->ReadPropertyFloat('TempMin');
                $max = $this->ReadPropertyFloat('TempMax');
                $this->WriteTo('TargetVar', max($min, min($max, (float) $Value)));
                break;
            case 'heatRelease':
                $this->WriteTo('HeatReleaseVar', (bool) $Value);
                break;
            case 'coolRelease':
                $this->WriteTo('CoolReleaseVar', (bool) $Value);
                break;
        }
    }

    public function GetVisualizationTile()
    {
        $html = file_get_contents(__DIR__ . '/module.html');
        $init = '<script>handleMessage(' . json_encode($this->GetFullMessage(), JSON_HEX_TAG) . ');</script>';
        return $html . $init;
    }

    // ---------------------------------------------------------------

    private function WriteTo(string $property, $value): void
    {
        $id = $this->ReadPropertyInteger($property);
        if ($id > 0 && IPS_VariableExists($id)) {
            RequestAction($id, $value);
        }
    }

    private function GetValues(): array
    {
        $values = [];
        foreach (self::VARS as $key => $prop) {
            $id = $this->ReadPropertyInteger($prop);
            $values[$key] = ($id > 0 && IPS_VariableExists($id)) ? GetValue($id) : null;
        }
        return $values;
    }

    private function GetFullMessage(): array
    {
        $has = [];
        foreach (self::VARS as $key => $prop) {
            $id = $this->ReadPropertyInteger($prop);
            $has[$key] = ($id > 0 && IPS_VariableExists($id));
        }

        $textStyles = [];
        foreach (self::TEXT_ELEMENTS as $key => [$property]) {
            $fallbackColor = in_array($key, ['name', 'season'], true) ? $this->Col('TextColor', null) : null;
            $textStyles[$key] = [
                'color'            => $this->Col('Text' . $property . 'Color', $fallbackColor),
                'fontFamily'       => $this->ReadPropertyString('Text' . $property . 'Font'),
                'fontSize'         => $this->ReadPropertyInteger('Text' . $property . 'Size'),
                'backgroundColor' => $this->Col('Text' . $property . 'BackgroundColor', null),
                'borderWidth'      => $this->ReadPropertyInteger('Text' . $property . 'BorderWidth'),
                'borderColor'      => $this->Col('Text' . $property . 'BorderColor', null),
                'image'            => [
                    'src'     => $this->GetMediaSource($this->ReadPropertyInteger('Text' . $property . 'Image')),
                    'opacity' => $this->ReadPropertyInteger('Text' . $property . 'ImageOpacity')
                ]
            ];
        }

        return [
            'config' => [
                'name'         => $this->ReadPropertyString('Name'),
                'namePosition' => $this->ReadPropertyString('NamePosition'),
                'textStyles'   => $textStyles,
                'labels'       => [
                    'actual' => $this->ReadPropertyString('LabelActual'),
                    'target' => $this->ReadPropertyString('LabelTarget'),
                    'heat'   => $this->ReadPropertyString('LabelHeat'),
                    'cool'   => $this->ReadPropertyString('LabelCool'),
                    'co2'    => $this->ReadPropertyString('LabelCO2'),
                    'vent'   => $this->ReadPropertyString('LabelVent')
                ],
                'has'          => $has,
                'seasonInvert' => $this->ReadPropertyBoolean('SeasonInvert'),
                'heatInvert'   => $this->ReadPropertyBoolean('HeatInvert'),
                'coolInvert'   => $this->ReadPropertyBoolean('CoolInvert'),
                'heatRelPos'   => $this->ReadPropertyString('HeatReleasePos'),
                'coolRelPos'   => $this->ReadPropertyString('CoolReleasePos'),
                'min'          => $this->ReadPropertyFloat('TempMin'),
                'max'          => $this->ReadPropertyFloat('TempMax'),
                'step'         => $this->ReadPropertyFloat('TempStep'),
                'co2Min'       => $this->ReadPropertyInteger('CO2Min'),
                'co2Max'       => $this->ReadPropertyInteger('CO2Max'),
                'colorCold'    => $this->Col('ColorCold', '#3B82F6'),
                'colorWarm'    => $this->Col('ColorWarm', '#F97316'),
                'colorVent'    => $this->Col('ColorVent', '#14B8A6'),
                'textColor'    => $this->Col('TextColor', null),
                'bgColor'      => $this->Col('BackgroundColor', null),
                'bgOpacity'    => $this->ReadPropertyInteger('BackgroundOpacity'),
                'image'        => $this->GetImage()
            ],
            'values' => $this->GetValues()
        ];
    }

    private function Col(string $property, ?string $fallback): ?string
    {
        $c = $this->ReadPropertyInteger($property);
        return ($c < 0) ? $fallback : sprintf('#%06X', $c);
    }

    private function GetImage(): array
    {
        $result = [
            'src'     => '',
            'pos'     => $this->ReadPropertyString('ImagePosition'),
            'size'    => $this->ReadPropertyInteger('ImageSize'),
            'opacity' => $this->ReadPropertyInteger('ImageOpacity')
        ];

        $id = $this->ReadPropertyInteger('BackgroundImage');
        $result['src'] = $this->GetMediaSource($id);
        return $result;
    }

    private function GetMediaSource(int $id): string
    {
        if ($id <= 0 || !IPS_MediaExists($id)) {
            return '';
        }

        $media = IPS_GetMedia($id);
        if ($media['MediaType'] !== MEDIATYPE_IMAGE) {
            return '';
        }

        $ext = strtolower(pathinfo($media['MediaFile'], PATHINFO_EXTENSION));
        $mimes = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
            'webp' => 'image/webp'
        ];
        $mime = $mimes[$ext] ?? 'image/png';
        return 'data:' . $mime . ';base64,' . IPS_GetMediaContent($id);
    }
}
