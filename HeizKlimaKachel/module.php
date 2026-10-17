<?php

declare(strict_types=1);

class HeizungKlimaKachel extends IPSModule
{
    private const TEXT_ELEMENTS = [
        'name'        => ['Name', 1.125],
        'season'      => ['Season', 0.9],
        'actual'      => ['Actual', 0.875],
        'target'      => ['Target', 0.875],
        'heat'        => ['Heat', 0.875],
        'vent'        => ['Vent', 0.875],
        'ventTarget'  => ['VentTarget', 0.875],
        'co2'         => ['CO2', 0.875],
        'cool'        => ['Cool', 0.875],
        'climateTarget' => ['ClimateTarget', 0.875],
        'fan'         => ['Fan', 0.875],
        'fanTarget'   => ['FanTarget', 0.875]
    ];

    // Schlüssel in der Kachel => Name der Eigenschaft (Variable)
    private const VARS = [
        'season'        => 'SeasonVar',
        'actual'        => 'ActualVar',
        'target'        => 'TargetVar',
        'valveActual'   => 'HeatPowerVar',
        'ventActive'    => 'VentActiveVar',
        'ventActual'    => 'VentPowerVar',
        'ventTarget'    => 'VentTargetVar',
        'co2'           => 'CO2Var',
        'climateActual' => 'CoolPowerVar',
        'climateTarget' => 'CoolTargetVar',
        'fanActual'     => 'CoolFanVar',
        'fanTarget'     => 'CoolFanTargetVar'
    ];

    private const PERMISSIONS = [
        'room'    => 'RoomPermissionVar',
        'vent'    => 'VentPermissionVar',
        'climate' => 'ClimatePermissionVar',
        'fan'     => 'FanPermissionVar'
    ];

    private const TARGET_CONTROLLERS = [
        'target'        => 'room',
        'ventTarget'    => 'vent',
        'climateTarget' => 'climate',
        'fanTarget'     => 'fan'
    ];

    private const DISPLAY_ELEMENTS = [
        'season'        => ['Season', 'Saison', 'Season', 'season', false, 'TextSeason'],
        'actual'        => ['Actual', 'Istwert', 'Room', 'actual', false, 'TextActual'],
        'target'        => ['Target', 'Sollwert', 'Room', 'target', true, 'TextTarget'],
        'valveActual'   => ['ValveActual', 'Ventilstellung', 'Valve', 'valveActual', false, 'TextHeat'],
        'ventActual'    => ['VentActual', 'Lüftung Istwert', 'Vent', 'ventActual', false, 'TextVent'],
        'ventTarget'    => ['VentTarget', 'Lüftung Sollwert', 'Vent', 'ventTarget', true, 'TextVentTarget'],
        'co2'           => ['CO2', 'CO₂', 'CO2', 'co2', false, 'TextCO2'],
        'climateActual' => ['ClimateActual', 'Kühlung Istwert', 'Climate', 'climateActual', false, 'TextCool'],
        'climateTarget' => ['ClimateTarget', 'Kühlung Sollwert', 'Climate', 'climateTarget', true, 'TextClimateTarget'],
        'fanActual'     => ['FanActual', 'Lüfterstufe Istwert', 'Fan', 'fanActual', false, 'TextFan'],
        'fanTarget'     => ['FanTarget', 'Lüfterstufe Sollwert', 'Fan', 'fanTarget', true, 'TextFanTarget']
    ];

    private const CHANNEL_DEFAULTS = [
        'Season'  => [0.0, 1.0, 0.5],
        'Room'    => [15.0, 30.0, 22.5],
        'Valve'   => [0.0, 100.0, 50.0],
        'Vent'    => [0.0, 100.0, 50.0],
        'CO2'     => [400.0, 2000.0, 1200.0],
        'Climate' => [15.0, 35.0, 25.0],
        'Fan'     => [0.0, 7.0, 3.5]
    ];

    private const CONTROL_DEFAULTS = [
        'room'    => [5.0, 35.0, 0.5],
        'vent'    => [0.0, 100.0, 1.0],
        'climate' => [15.0, 35.0, 0.5],
        'fan'     => [0.0, 7.0, 1.0]
    ];

    public function Create()
    {
        parent::Create();

        // Namen
        $this->RegisterPropertyString('Name', 'Wohnzimmer');
        $this->RegisterPropertyString('NamePosition', 'top-left');
        $this->RegisterPropertyInteger('NameOffsetX', 0);
        $this->RegisterPropertyInteger('NameOffsetY', 0);

        $defaultPositions = [
            'season' => [50, 8], 'actual' => [50, 20], 'target' => [50, 29],
            'valveActual' => [50, 38], 'ventActual' => [50, 47],
            'ventTarget' => [50, 56], 'co2' => [50, 65],
            'climateActual' => [50, 74], 'climateTarget' => [50, 82],
            'fanActual' => [50, 90], 'fanTarget' => [50, 96]
        ];
        foreach (self::DISPLAY_ELEMENTS as $key => [$property]) {
            $position = $defaultPositions[$key];
            $this->RegisterPropertyString('Display' . $property . 'Shape', 'straight');
            $this->RegisterPropertyString('Display' . $property . 'Orientation', 'horizontal');
            $this->RegisterPropertyInteger('Position' . $property . 'X', $position[0]);
            $this->RegisterPropertyInteger('Position' . $property . 'Y', $position[1]);
            $this->RegisterPropertyInteger('Display' . $property . 'Width', 78);
            $this->RegisterPropertyInteger('Display' . $property . 'StartAngle', 0);
            $this->RegisterPropertyInteger('Display' . $property . 'SweepAngle', 180);
            $this->RegisterPropertyInteger('Display' . $property . 'BorderWidth', 0);
            $this->RegisterPropertyInteger('Display' . $property . 'BorderColor', -1);
        }
        foreach (self::CHANNEL_DEFAULTS as $channel => [$min, $max, $mid]) {
            $this->RegisterPropertyFloat('Range' . $channel . 'Min', $min);
            $this->RegisterPropertyFloat('Range' . $channel . 'Max', $max);
            $this->RegisterPropertyFloat('Range' . $channel . 'Mid', $mid);
        }
        foreach (self::PERMISSIONS as $permission => $property) {
            [$controlMin, $controlMax, $controlStep] = self::CONTROL_DEFAULTS[$permission];
            $this->RegisterPropertyInteger($property, 0);
            $this->RegisterPropertyBoolean(ucfirst($permission) . 'ControlEnabled', true);
            $this->RegisterPropertyFloat(ucfirst($permission) . 'ControlMin', $controlMin);
            $this->RegisterPropertyFloat(ucfirst($permission) . 'ControlMax', $controlMax);
            $this->RegisterPropertyFloat(ucfirst($permission) . 'ControlStep', $controlStep);
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
        $this->RegisterPropertyBoolean('FanInvert', false);

        // Farben (-1 = transparent / Standard)
        $this->RegisterPropertyInteger('ColorCold', hexdec('3B82F6'));
        $this->RegisterPropertyInteger('ColorWarm', hexdec('F97316'));
        $this->RegisterPropertyInteger('ColorVent', hexdec('14B8A6'));
        $this->RegisterPropertyInteger('TextColor', -1);
        foreach (self::DISPLAY_ELEMENTS as [$property, $_label, $channel]) {
            [$lowColor, $highColor] = $this->GetDisplayColorDefaults($channel);
            $this->RegisterPropertyInteger('Color' . $property . 'Low', $lowColor);
            $this->RegisterPropertyInteger('Color' . $property . 'High', $highColor);
        }

        // Hintergrund
        $this->RegisterPropertyInteger('BackgroundColor', -1);
        $this->RegisterPropertyInteger('BackgroundOpacity', 100);
        $this->RegisterPropertyInteger('BackgroundImage', 0);
        $this->RegisterPropertyString('ImagePosition', 'full');
        $this->RegisterPropertyInteger('ImageSize', 50);
        $this->RegisterPropertyInteger('ImageOpacity', 40);

        foreach (self::DISPLAY_ELEMENTS as $key => [$property]) {
            $this->RegisterPropertyString('Label' . $property, self::DISPLAY_ELEMENTS[$key][1]);
        }

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
        foreach (self::PERMISSIONS as $prop) {
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
        if (strpos((string) $Ident, 'setTarget:') === 0) {
            if (!is_numeric($Value)) {
                throw new InvalidArgumentException('Target values must be numeric.');
            }
            $this->SetTarget(substr((string) $Ident, strlen('setTarget:')), (float) $Value);
            return;
        }

        switch ($Ident) {
            case 'ResetAppearance':
                $this->ResetAppearance();
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

    private function ResetAppearance(): void
    {
        $defaults = [
            'NamePosition'     => 'top-left',
            'NameOffsetX'      => 0,
            'NameOffsetY'      => 0,
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
        foreach (self::DISPLAY_ELEMENTS as [$property, $_label, $channel]) {
            [$lowColor, $highColor] = $this->GetDisplayColorDefaults($channel);
            $defaults['Color' . $property . 'Low'] = $lowColor;
            $defaults['Color' . $property . 'High'] = $highColor;
        }

        $defaultPositions = [
            'season' => [50, 8], 'actual' => [50, 20], 'target' => [50, 29],
            'valveActual' => [50, 38], 'ventActual' => [50, 47],
            'ventTarget' => [50, 56], 'co2' => [50, 65],
            'climateActual' => [50, 74], 'climateTarget' => [50, 82],
            'fanActual' => [50, 90], 'fanTarget' => [50, 96]
        ];
        foreach (self::DISPLAY_ELEMENTS as $key => [$property]) {
            $defaults['Display' . $property . 'Shape'] = 'straight';
            $defaults['Display' . $property . 'Orientation'] = 'horizontal';
            $defaults['Position' . $property . 'X'] = $defaultPositions[$key][0];
            $defaults['Position' . $property . 'Y'] = $defaultPositions[$key][1];
            $defaults['Display' . $property . 'Width'] = 78;
            $defaults['Display' . $property . 'StartAngle'] = 0;
            $defaults['Display' . $property . 'SweepAngle'] = 180;
            $defaults['Display' . $property . 'BorderWidth'] = 0;
            $defaults['Display' . $property . 'BorderColor'] = -1;
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

    private function SetTarget(string $key, float $requestedValue): void
    {
        if (!is_finite($requestedValue)) {
            throw new InvalidArgumentException('Target values must be finite.');
        }
        if (!isset(self::TARGET_CONTROLLERS[$key], self::VARS[$key])) {
            throw new InvalidArgumentException('Unknown target element: ' . $key);
        }

        $permission = self::TARGET_CONTROLLERS[$key];
        $prefix = ucfirst($permission);
        if (!$this->ReadPropertyBoolean($prefix . 'ControlEnabled')) {
            return;
        }

        $permissionID = $this->ReadPropertyInteger(self::PERMISSIONS[$permission]);
        if ($permissionID > 0 && !$this->IsPermissionGranted($permissionID)) {
            return;
        }

        $min = $this->ReadPropertyFloat($prefix . 'ControlMin');
        $max = $this->ReadPropertyFloat($prefix . 'ControlMax');
        $step = $this->ReadPropertyFloat($prefix . 'ControlStep');
        if ($max <= $min || $step <= 0) {
            throw new RuntimeException('Invalid control range configured for ' . $permission . '.');
        }
        $value = max($min, min($max, $requestedValue));
        $value = $min + round(($value - $min) / $step) * $step;
        $value = max($min, min($max, $value));
        if ($key === 'fanTarget' && $this->ReadPropertyBoolean('FanInvert')) {
            $value = $min + $max - $value;
        }
        $id = $this->ReadPropertyInteger(self::VARS[$key]);
        if ($id <= 0 || !IPS_VariableExists($id)) {
            throw new RuntimeException('The target variable for ' . $key . ' is not configured or does not exist.');
        }
        $variable = IPS_GetVariable($id);
        if ($variable['VariableType'] === 1) {
            RequestAction($id, (int) round($value));
        } elseif ($variable['VariableType'] === 2) {
            RequestAction($id, $value);
        } else {
            throw new RuntimeException('Target variables must be integer or float variables.');
        }
    }

    private function GetValues(): array
    {
        $values = [];
        foreach (self::VARS as $key => $prop) {
            $id = $this->ReadPropertyInteger($prop);
            $values[$key] = ($id > 0 && IPS_VariableExists($id)) ? GetValue($id) : null;
        }
        if ($this->ReadPropertyBoolean('FanInvert') && is_numeric($values['fanTarget'])) {
            $min = $this->ReadPropertyFloat('FanControlMin');
            $max = $this->ReadPropertyFloat('FanControlMax');
            $values['fanTarget'] = $min + $max - (float) $values['fanTarget'];
        }
        foreach (self::PERMISSIONS as $key => $prop) {
            $id = $this->ReadPropertyInteger($prop);
            $values[$key . 'Permission'] = ($id <= 0) || $this->IsPermissionGranted($id);
        }
        return $values;
    }

    private function GetFullMessage(): array
    {
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

        $elements = [];
        foreach (self::DISPLAY_ELEMENTS as $key => [$property, $defaultLabel, $channel, $valueKey, $isTarget, $stylePrefix]) {
            $rangeMin = $this->ReadPropertyFloat('Range' . $channel . 'Min');
            $rangeMax = $this->ReadPropertyFloat('Range' . $channel . 'Max');
            $element = [
                'label' => $this->ReadPropertyString('Label' . $property),
                'available' => $key === 'season' ? $this->HasVariable('SeasonVar') : $this->HasVariable(self::VARS[$valueKey]),
                'valueKey' => $valueKey,
                'target' => $isTarget,
                'rangeMin' => $rangeMin,
                'rangeMax' => $rangeMax,
                'mid' => $this->ReadPropertyFloat('Range' . $channel . 'Mid'),
                'shape' => $this->ReadPropertyString('Display' . $property . 'Shape'),
                'orientation' => $this->ReadPropertyString('Display' . $property . 'Orientation'),
                'x' => $this->ReadPropertyInteger('Position' . $property . 'X'),
                'y' => $this->ReadPropertyInteger('Position' . $property . 'Y'),
                'width' => $this->ReadPropertyInteger('Display' . $property . 'Width'),
                'start' => $this->ReadPropertyInteger('Display' . $property . 'StartAngle'),
                'sweep' => $this->ReadPropertyInteger('Display' . $property . 'SweepAngle'),
                'borderWidth' => $this->ReadPropertyInteger('Display' . $property . 'BorderWidth'),
                'borderColor' => $this->Col('Display' . $property . 'BorderColor', null),
                'styles' => $this->GetDisplayStyles($textStyles, $key, $stylePrefix),
                'lowColor' => $this->Col('Color' . $property . 'Low', '#3B82F6'),
                'highColor' => $this->Col('Color' . $property . 'High', '#F97316'),
                'step' => 1.0,
                'controllable' => false,
                'permissionKey' => null,
                'invert' => $key === 'fanTarget' && $this->ReadPropertyBoolean('FanInvert')
            ];

            if ($isTarget) {
                $permission = self::TARGET_CONTROLLERS[$key];
                $prefix = ucfirst($permission);
                $element['controlMin'] = $this->ReadPropertyFloat($prefix . 'ControlMin');
                $element['controlMax'] = $this->ReadPropertyFloat($prefix . 'ControlMax');
                $element['step'] = $this->ReadPropertyFloat($prefix . 'ControlStep');
                $element['controllable'] = $this->ReadPropertyBoolean($prefix . 'ControlEnabled');
                $element['permissionKey'] = $permission . 'Permission';
                if ($key === 'fanTarget') {
                    $ratio = $rangeMax > $rangeMin
                        ? max(0.0, min(1.0, ($element['mid'] - $rangeMin) / ($rangeMax - $rangeMin)))
                        : 0.5;
                    $element['rangeMin'] = $element['controlMin'];
                    $element['rangeMax'] = $element['controlMax'];
                    $element['mid'] = $element['controlMin'] +
                        $ratio * ($element['controlMax'] - $element['controlMin']);
                }
            }
            $elements[$key] = $element;
        }

        return [
            'config' => [
                'name'         => $this->ReadPropertyString('Name'),
                'namePosition' => $this->ReadPropertyString('NamePosition'),
                'nameOffsetX'  => $this->ReadPropertyInteger('NameOffsetX'),
                'nameOffsetY'  => $this->ReadPropertyInteger('NameOffsetY'),
                'elements' => $elements,
                'textStyles'   => $textStyles,
                'seasonInvert' => $this->ReadPropertyBoolean('SeasonInvert'),
                'heatInvert'   => $this->ReadPropertyBoolean('HeatInvert'),
                'coolInvert'   => $this->ReadPropertyBoolean('CoolInvert'),
                'fanInvert'    => $this->ReadPropertyBoolean('FanInvert'),
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

    private function HasVariable(string $property): bool
    {
        $id = $this->ReadPropertyInteger($property);
        return $id > 0 && IPS_VariableExists($id);
    }

    private function IsPermissionGranted(int $id): bool
    {
        if (!IPS_VariableExists($id)) {
            return false;
        }
        $variable = IPS_GetVariable($id);
        return $variable['VariableType'] === 0 && (bool) GetValue($id);
    }

    private function GetDisplayColorDefaults(string $channel): array
    {
        if ($channel === 'CO2') {
            return [hexdec('22C55E'), hexdec('EF4444')];
        }
        if ($channel === 'Vent') {
            return [hexdec('14B8A6'), hexdec('14B8A6')];
        }
        return [hexdec('3B82F6'), hexdec('F97316')];
    }

    private function GetDisplayStyles(array $textStyles, string $key, string $stylePrefix): array
    {
        $base = $key === 'co2' ? 'co2' : lcfirst(substr($stylePrefix, 4));
        $style = $textStyles[$base] ?? [];
        return [
            'label' => $style,
            'value' => $style
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
