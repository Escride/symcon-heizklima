<?php

declare(strict_types=1);

class HeizungKlimaKachel extends IPSModule
{
    private const TEXT_ELEMENTS = [
        'name'        => ['Name', 1.125],
        'season'      => ['Season', 0.9],
        'actualLabel' => ['ActualLabel', 0.875],
        'actualValue' => ['ActualValue', 0.875],
        'targetLabel' => ['TargetLabel', 0.875],
        'targetValue' => ['TargetValue', 0.875],
        'heatLabel'   => ['HeatLabel', 0.875],
        'heatValue'   => ['HeatValue', 0.875],
        'coolLabel'   => ['CoolLabel', 0.875],
        'coolValue'   => ['CoolValue', 0.875],
        'co2Label'    => ['CO2Label', 0.875],
        'co2Value'   => ['CO2Value', 0.875],
        'ventLabel'   => ['VentLabel', 0.875],
        'ventValue'   => ['VentValue', 0.875]
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

    private const LAYOUT_GROUPS = [
        'season' => 'Season',
        'actual' => 'Actual',
        'target' => 'Target',
        'heat'   => 'Heat',
        'cool'   => 'Cool',
        'co2'    => 'CO2',
        'vent'   => 'Vent'
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
        $this->RegisterPropertyInteger('NameOffsetX', 0);
        $this->RegisterPropertyInteger('NameOffsetY', 0);

        $this->RegisterPropertyString('DisplayLayout', 'standard');
        $this->RegisterPropertyString('LayoutPreset', 'standard');
        $this->RegisterPropertyInteger('LayoutElementWidth', 42);
        $this->RegisterPropertyString('ScaleShape', 'straight');
        $this->RegisterPropertyInteger('ScaleStartAngle', 0);
        $this->RegisterPropertyInteger('ScaleSweepAngle', 180);
        $defaultPositions = [
            'season' => [50, 10],
            'actual' => [24, 35],
            'target' => [76, 35],
            'heat'   => [24, 60],
            'cool'   => [76, 60],
            'co2'    => [24, 85],
            'vent'   => [76, 85]
        ];
        foreach (self::LAYOUT_GROUPS as $key => $property) {
            $this->RegisterPropertyInteger('Position' . $property . 'X', $defaultPositions[$key][0]);
            $this->RegisterPropertyInteger('Position' . $property . 'Y', $defaultPositions[$key][1]);
        }

        foreach (self::TEXT_ELEMENTS as [$property, $fontSize]) {
            $this->RegisterPropertyInteger('Text' . $property . 'Color', 0);
            $this->RegisterPropertyString('Text' . $property . 'Font', '');
            $this->RegisterPropertyFloat('Text' . $property . 'Size', $fontSize);
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
            case 'ResetAppearance':
                $this->ResetAppearance();
                break;
            case 'ApplyLayoutPreset':
                $this->ApplyLayoutPreset((string) $Value);
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

    private function ResetAppearance(): void
    {
        $defaults = [
            'NamePosition'     => 'top-left',
            'NameOffsetX'      => 0,
            'NameOffsetY'      => 0,
            'DisplayLayout'    => 'standard',
            'LayoutPreset'     => 'standard',
            'LayoutElementWidth' => 42,
            'ScaleShape'       => 'straight',
            'ScaleStartAngle'  => 0,
            'ScaleSweepAngle'  => 180,
            'ColorCold'        => hexdec('3B82F6'),
            'ColorWarm'        => hexdec('F97316'),
            'ColorVent'        => hexdec('14B8A6'),
            'TextColor'        => -1,
            'BackgroundColor'  => -1,
            'BackgroundOpacity' => 100,
            'BackgroundImage'  => 0,
            'ImagePosition'    => 'full',
            'ImageSize'        => 50,
            'ImageOpacity'     => 40
        ];

        foreach (self::TEXT_ELEMENTS as [$property, $fontSize]) {
            $defaults['Text' . $property . 'Color'] = 0;
            $defaults['Text' . $property . 'Font'] = '';
            $defaults['Text' . $property . 'Size'] = $fontSize;
            $defaults['Text' . $property . 'BackgroundColor'] = -1;
            $defaults['Text' . $property . 'BorderWidth'] = 0;
            $defaults['Text' . $property . 'BorderColor'] = -1;
            $defaults['Text' . $property . 'Image'] = 0;
            $defaults['Text' . $property . 'ImageOpacity'] = 100;
        }

        $defaultPositions = [
            'season' => [50, 10],
            'actual' => [24, 35],
            'target' => [76, 35],
            'heat'   => [24, 60],
            'cool'   => [76, 60],
            'co2'    => [24, 85],
            'vent'   => [76, 85]
        ];
        foreach (self::LAYOUT_GROUPS as $key => $property) {
            $defaults['Position' . $property . 'X'] = $defaultPositions[$key][0];
            $defaults['Position' . $property . 'Y'] = $defaultPositions[$key][1];
        }

        foreach ($defaults as $property => $value) {
            if (!IPS_SetProperty($this->InstanceID, $property, (string) $value)) {
                throw new RuntimeException('Could not reset appearance property: ' . $property);
            }
        }

        if (!IPS_ApplyChanges($this->InstanceID)) {
            throw new RuntimeException('Could not apply appearance defaults.');
        }
    }

    private function ApplyLayoutPreset(string $preset): void
    {
        $presets = [
            'standard' => ['layout' => 'standard', 'shape' => 'straight', 'start' => 0, 'sweep' => 180, 'width' => 42],
            'half-right' => ['layout' => 'free', 'shape' => 'arc', 'start' => 0, 'sweep' => 180, 'width' => 42],
            'half-left' => ['layout' => 'free', 'shape' => 'arc', 'start' => 180, 'sweep' => 180, 'width' => 42],
            'quarter-upper-right' => ['layout' => 'free', 'shape' => 'arc', 'start' => 0, 'sweep' => 90, 'width' => 42],
            'quarter-lower-right' => ['layout' => 'free', 'shape' => 'arc', 'start' => 90, 'sweep' => 90, 'width' => 42],
            'quarter-lower-left' => ['layout' => 'free', 'shape' => 'arc', 'start' => 180, 'sweep' => 90, 'width' => 42],
            'quarter-upper-left' => ['layout' => 'free', 'shape' => 'arc', 'start' => 270, 'sweep' => 90, 'width' => 42]
        ];

        if (!isset($presets[$preset])) {
            throw new InvalidArgumentException('Unknown layout preset: ' . $preset);
        }

        $settings = $presets[$preset];
        $properties = [
            'DisplayLayout' => $settings['layout'],
            'LayoutPreset' => $preset,
            'LayoutElementWidth' => $settings['width'],
            'ScaleShape' => $settings['shape'],
            'ScaleStartAngle' => $settings['start'],
            'ScaleSweepAngle' => $settings['sweep']
        ];

        $defaultPositions = [
            'season' => [50, 10],
            'actual' => [24, 35],
            'target' => [76, 35],
            'heat'   => [24, 60],
            'cool'   => [76, 60],
            'co2'    => [24, 85],
            'vent'   => [76, 85]
        ];
        foreach (self::LAYOUT_GROUPS as $key => $property) {
            $properties['Position' . $property . 'X'] = $defaultPositions[$key][0];
            $properties['Position' . $property . 'Y'] = $defaultPositions[$key][1];
        }

        foreach ($properties as $property => $value) {
            if (!IPS_SetProperty($this->InstanceID, $property, (string) $value)) {
                throw new RuntimeException('Could not apply layout preset property: ' . $property);
            }
        }

        if (!IPS_ApplyChanges($this->InstanceID)) {
            throw new RuntimeException('Could not apply layout preset.');
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
            $fontSize = $this->ReadPropertyFloat('Text' . $property . 'Size');
            if ($fontSize > 4) {
                $fontSize /= 16;
            }
            $textStyles[$key] = [
                'color'           => $this->TextCol('Text' . $property . 'Color'),
                'fontFamily'      => $this->ReadPropertyString('Text' . $property . 'Font'),
                'fontSize'        => $fontSize,
                'backgroundColor' => $this->Col('Text' . $property . 'BackgroundColor', null),
                'borderWidth'      => $this->ReadPropertyInteger('Text' . $property . 'BorderWidth'),
                'borderColor'      => $this->Col('Text' . $property . 'BorderColor', null),
                'image'            => [
                    'src'     => $this->GetMediaSource($this->ReadPropertyInteger('Text' . $property . 'Image')),
                    'opacity' => $this->ReadPropertyInteger('Text' . $property . 'ImageOpacity')
                ]
            ];
        }

        $positions = [];
        foreach (self::LAYOUT_GROUPS as $key => $property) {
            $positions[$key] = [
                'x' => $this->ReadPropertyInteger('Position' . $property . 'X'),
                'y' => $this->ReadPropertyInteger('Position' . $property . 'Y')
            ];
        }

        return [
            'config' => [
                'name'         => $this->ReadPropertyString('Name'),
                'namePosition' => $this->ReadPropertyString('NamePosition'),
                'nameOffsetX'  => $this->ReadPropertyInteger('NameOffsetX'),
                'nameOffsetY'  => $this->ReadPropertyInteger('NameOffsetY'),
                'displayLayout' => $this->ReadPropertyString('DisplayLayout'),
                'layoutElementWidth' => $this->ReadPropertyInteger('LayoutElementWidth'),
                'scaleShape' => $this->ReadPropertyString('ScaleShape'),
                'scaleStartAngle' => $this->ReadPropertyInteger('ScaleStartAngle'),
                'scaleSweepAngle' => $this->ReadPropertyInteger('ScaleSweepAngle'),
                'positions' => $positions,
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

    private function TextCol(string $property): string
    {
        $color = $this->ReadPropertyInteger($property);
        return ($color < 0) ? 'transparent' : sprintf('#%06X', $color);
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
