<?php

declare(strict_types=1);

class BBModuleKlima extends IPSModule
{
    private const TEXT_ELEMENTS = [
        'name'       => ['Name', 1.125],
        'actual'     => ['Actual', 0.875],
        'target'     => ['Target', 0.875],
        'output'     => ['Output', 0.875],
        'extra'      => ['Extra', 0.875],
        'permission' => ['Permission', 0.875]
    ];

    private const VARS = [
        'actual' => 'ActualVar',
        'target' => 'TargetVar',
        'output' => 'OutputVar',
        'extra'  => 'ExtraVar'
    ];

    private const DISPLAY_ELEMENTS = [
        'actual' => ['Actual', 'Istwert', 'Target', 'actual', false, 'TextActual'],
        'target' => ['Target', 'Sollwert', 'Target', 'target', true, 'TextTarget'],
        'output' => ['Output', 'Stellwert / Leistung', 'Output', 'output', false, 'TextOutput'],
        'extra'  => ['Extra', 'Zusatzwert', 'Extra', 'extra', false, 'TextExtra']
    ];

    private const COLOR_PALETTES = [
        'symcon' => ['F2F5F8', '17212B', '2774AE', 'E69A36'],
        'classic' => ['EFE7D3', '392F24', '447744', 'BD542B'],
        'modern' => ['F3F4F6', '111827', '2563EB', 'F97316'],
        'futuristic' => ['0B1020', 'EAF2FF', '00D9FF', '8B5CF6'],
        'neon-green-orange' => ['101710', 'E7FFE5', '39FF14', 'FF6A00'],
        'neon-blue-yellow' => ['0A1020', 'E8F4FF', '00A6FF', 'FFE600'],
        'neon-pink-cyan' => ['180D1A', 'FFF0FF', 'FF2BD6', '00F5FF'],
        'neon-purple-green' => ['120D1D', 'F2EFFF', 'B026FF', '39FF14'],
        'neon-red-cyan' => ['1A0D10', 'FFF0F0', 'FF1744', '00F5FF'],
        'neon-orange-purple' => ['1B1009', 'FFF4E8', 'FF6A00', 'B026FF'],
        'neon-green-pink' => ['0D1711', 'EDFFF2', '39FF14', 'FF2BD6'],
        'neon-blue-orange' => ['0C1220', 'EDF5FF', '00A6FF', 'FF6A00']
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
            'actual' => [50, 35],
            'target' => [50, 55],
            'output' => [50, 72],
            'extra'  => [50, 88]
        ];
        foreach (self::DISPLAY_ELEMENTS as $key => [$property]) {
            $position = $defaultPositions[$key];
            $this->RegisterPropertyString('Display' . $property . 'Shape', $key === 'target' ? 'bar' : 'text');
            $this->RegisterPropertyString('Display' . $property . 'Orientation', 'horizontal');
            $this->RegisterPropertyInteger('Position' . $property . 'X', $position[0]);
            $this->RegisterPropertyInteger('Position' . $property . 'Y', $position[1]);
            $this->RegisterPropertyInteger('Display' . $property . 'Width', 78);
            $this->RegisterPropertyInteger('Display' . $property . 'StartAngle', 0);
            $this->RegisterPropertyInteger('Display' . $property . 'SweepAngle', 180);
            $this->RegisterPropertyInteger('Display' . $property . 'BorderWidth', 0);
            $this->RegisterPropertyInteger('Display' . $property . 'BorderColor', -1);
            $this->RegisterPropertyInteger('Display' . $property . 'TextOffsetX', 0);
            $this->RegisterPropertyInteger('Display' . $property . 'TextOffsetY', 0);
        }
        $this->RegisterPropertyFloat('RoomControlMin', 5.0);
        $this->RegisterPropertyFloat('RoomControlMax', 35.0);
        $this->RegisterPropertyFloat('RoomControlStep', 0.5);
        $this->RegisterPropertyBoolean('RoomControlEnabled', true);
        $this->RegisterPropertyInteger('RoomPermissionVar', 0);
        $this->RegisterPropertyInteger('PermissionVar', 0);
        $this->RegisterPropertyString('LabelPermission', 'Freigabe');
        $this->RegisterPropertyInteger('PositionPermissionX', 88);
        $this->RegisterPropertyInteger('PositionPermissionY', 8);
        $this->RegisterPropertyInteger('DisplayPermissionTextOffsetX', 0);
        $this->RegisterPropertyInteger('DisplayPermissionTextOffsetY', 0);
        foreach (['Output' => [0.0, 100.0, 50.0], 'Extra' => [0.0, 100.0, 50.0]] as $slot => [$min, $max, $mid]) {
            $this->RegisterPropertyFloat('Range' . $slot . 'Min', $min);
            $this->RegisterPropertyFloat('Range' . $slot . 'Max', $max);
            $this->RegisterPropertyFloat('Range' . $slot . 'Mid', $mid);
        }
        $this->RegisterPropertyFloat('RangeRoomTargetMid', 20.0);

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
        foreach (self::DISPLAY_ELEMENTS as [$property]) {
            $this->RegisterPropertyInteger('Color' . $property . 'Low', hexdec('3B82F6'));
            $this->RegisterPropertyInteger('Color' . $property . 'High', hexdec('F97316'));
        }

        // Hintergrund
        $this->RegisterPropertyInteger('BackgroundColor', -1);
        $this->RegisterPropertyInteger('BackgroundOpacity', 100);
        $this->RegisterPropertyInteger('BackgroundImage', 0);
        $this->RegisterPropertyString('ImagePosition', 'full');
        $this->RegisterPropertyInteger('ImageSize', 50);
        $this->RegisterPropertyInteger('ImageOpacity', 40);
        $this->RegisterPropertyString('ColorPalette', 'symcon');

        foreach (self::DISPLAY_ELEMENTS as $key => [$property, $label]) {
            $this->RegisterPropertyString('Label' . $property, $label);
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
        $permissionID = $this->ReadPropertyInteger('PermissionVar');
        if ($permissionID <= 0) {
            $permissionID = $this->ReadPropertyInteger('RoomPermissionVar');
        }
        if ($permissionID > 0 && IPS_VariableExists($permissionID)) {
            $this->RegisterMessage($permissionID, VM_UPDATE);
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
        if (strpos((string) $Ident, 'ApplyLayoutPreset:') === 0) {
            $this->ApplyLayoutPreset(substr((string) $Ident, strlen('ApplyLayoutPreset:')));
            return;
        }

        switch ($Ident) {
            case 'ResetAppearance':
                $this->ResetAppearance();
                break;
            case 'SetPalette':
                if (!is_string($Value)) {
                    throw new InvalidArgumentException('Palette name must be a string.');
                }
                $this->SetPalette($Value);
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
            'BackgroundColor'  => -1,
            'BackgroundOpacity' => 100,
            'BackgroundImage'  => 0,
            'ImagePosition'    => 'full',
            'ImageSize'        => 50,
            'ImageOpacity'     => 40,
            'ColorPalette'     => 'symcon',
            'PositionPermissionX' => 88,
            'PositionPermissionY' => 8,
            'DisplayPermissionTextOffsetX' => 0,
            'DisplayPermissionTextOffsetY' => 0
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
        foreach (self::DISPLAY_ELEMENTS as [$property]) {
            $defaults['Color' . $property . 'Low'] = hexdec('3B82F6');
            $defaults['Color' . $property . 'High'] = hexdec('F97316');
        }

        $defaultPositions = [
            'actual' => [50, 35], 'target' => [50, 55],
            'output' => [50, 72], 'extra' => [50, 88]
        ];
        foreach (self::DISPLAY_ELEMENTS as $key => [$property]) {
            $defaults['Display' . $property . 'Shape'] = $key === 'target' ? 'bar' : 'text';
            $defaults['Display' . $property . 'Orientation'] = 'horizontal';
            $defaults['Position' . $property . 'X'] = $defaultPositions[$key][0];
            $defaults['Position' . $property . 'Y'] = $defaultPositions[$key][1];
            $defaults['Display' . $property . 'Width'] = 78;
            $defaults['Display' . $property . 'StartAngle'] = 0;
            $defaults['Display' . $property . 'SweepAngle'] = 180;
            $defaults['Display' . $property . 'BorderWidth'] = 0;
            $defaults['Display' . $property . 'BorderColor'] = -1;
            $defaults['Display' . $property . 'TextOffsetX'] = 0;
            $defaults['Display' . $property . 'TextOffsetY'] = 0;
        }
        foreach ($defaults as $property => $value) {
            if (!IPS_SetProperty($this->InstanceID, $property, (string) $value)) {
                throw new RuntimeException('Could not reset appearance property: ' . $property);
            }
        }

        $this->WritePaletteProperties('symcon');
        if (!IPS_ApplyChanges($this->InstanceID)) {
            throw new RuntimeException('Could not apply appearance defaults.');
        }
    }

    private function SetPalette(string $palette): void
    {
        $this->WritePaletteProperties($palette);
        if (!IPS_ApplyChanges($this->InstanceID)) {
            throw new RuntimeException('Could not apply color palette: ' . $palette);
        }
    }

    private function WritePaletteProperties(string $palette): void
    {
        if (!isset(self::COLOR_PALETTES[$palette])) {
            throw new InvalidArgumentException('Unknown color palette: ' . $palette);
        }
        [$background, $text, $low, $high] = self::COLOR_PALETTES[$palette];
        $properties = [
            'ColorPalette' => $palette,
            'BackgroundColor' => hexdec($background),
            'BackgroundOpacity' => 100
        ];
        foreach (self::DISPLAY_ELEMENTS as [$property]) {
            $properties['Color' . $property . 'Low'] = hexdec($low);
            $properties['Color' . $property . 'High'] = hexdec($high);
        }
        foreach (self::TEXT_ELEMENTS as [$property]) {
            $properties['Text' . $property . 'Color'] = hexdec($text);
        }
        foreach ($properties as $property => $value) {
            if (!IPS_SetProperty($this->InstanceID, $property, (string) $value)) {
                throw new RuntimeException('Could not set palette property: ' . $property);
            }
        }
    }

    private function ApplyLayoutPreset(string $arguments): void
    {
        $layouts = ['bars-stacked', 'bars-side', 'arcs-stacked', 'arcs-side'];
        if (!in_array($arguments, $layouts, true)) {
            throw new InvalidArgumentException('Unknown layout preset: ' . $arguments);
        }
        $keys = array_keys(self::DISPLAY_ELEMENTS);
        if ($arguments === 'bars-side' || $arguments === 'arcs-side') {
            $positions = [13, 38, 62, 87];
            $yPositions = array_fill(0, $count, 50);
        } else {
            $positions = array_fill(0, $count, 50);
            $yPositions = [15, 38, 62, 85];
        }

        foreach ($keys as $index => $key) {
            [$property] = self::DISPLAY_ELEMENTS[$key];
            $isArc = strpos($arguments, 'arcs-') === 0;
            $isSide = strpos($arguments, '-side') !== false;
            $settings = [
                'Display' . $property . 'Shape' => $isArc ? 'arc' : 'bar',
                'Display' . $property . 'Orientation' => $isSide && !$isArc ? 'vertical' : 'horizontal',
                'Display' . $property . 'Width' => $isArc
                    ? ($isSide ? 22 : 25)
                    : ($isSide ? 75 : 80),
                'Position' . $property . 'X' => $positions[$index],
                'Position' . $property . 'Y' => $yPositions[$index],
                'Display' . $property . 'StartAngle' => 270,
                'Display' . $property . 'SweepAngle' => 180,
                'Display' . $property . 'TextOffsetX' => 0,
                'Display' . $property . 'TextOffsetY' => 0
            ];
            foreach ($settings as $propertyName => $value) {
                if (!IPS_SetProperty($this->InstanceID, $propertyName, (string) $value)) {
                    throw new RuntimeException('Could not apply layout property: ' . $propertyName);
                }
            }
        }
        if (!IPS_ApplyChanges($this->InstanceID)) {
            throw new RuntimeException('Could not apply layout preset: ' . $arguments);
        }
    }

    private function SetTarget(string $key, float $requestedValue): void
    {
        if (!is_finite($requestedValue)) {
            throw new InvalidArgumentException('Target values must be finite.');
        }
        if ($key !== 'target') {
            throw new InvalidArgumentException('Unknown target element: ' . $key);
        }

        if (!$this->ReadPropertyBoolean('RoomControlEnabled')) {
            return;
        }

        $permissionID = $this->ReadPropertyInteger('PermissionVar');
        if ($permissionID <= 0) {
            $permissionID = $this->ReadPropertyInteger('RoomPermissionVar');
        }
        if ($permissionID > 0 && !$this->IsPermissionGranted($permissionID)) {
            return;
        }

        $min = $this->ReadPropertyFloat('RoomControlMin');
        $max = $this->ReadPropertyFloat('RoomControlMax');
        $step = $this->ReadPropertyFloat('RoomControlStep');
        if ($max <= $min || $step <= 0) {
            throw new RuntimeException('Invalid target range or step configured.');
        }
        $value = max($min, min($max, $requestedValue));
        $value = $min + round(($value - $min) / $step) * $step;
        $value = max($min, min($max, $value));
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
        $permissionID = $this->ReadPropertyInteger('PermissionVar');
        if ($permissionID <= 0) {
            $permissionID = $this->ReadPropertyInteger('RoomPermissionVar');
        }
        $values['permission'] = ($permissionID > 0 && IPS_VariableExists($permissionID))
            ? $this->IsPermissionGranted($permissionID)
            : null;
        $values['permissionAllowed'] = $values['permission'] !== false;
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
            if ($key === 'actual' || $key === 'target') {
                $rangeMin = $this->ReadPropertyFloat('RoomControlMin');
                $rangeMax = $this->ReadPropertyFloat('RoomControlMax');
                $mid = $this->ReadPropertyFloat('RangeRoomTargetMid');
            } else {
                $rangeMin = $this->ReadPropertyFloat('Range' . $channel . 'Min');
                $rangeMax = $this->ReadPropertyFloat('Range' . $channel . 'Max');
                $mid = $this->ReadPropertyFloat('Range' . $channel . 'Mid');
            }
            $element = [
                'label' => $this->ReadPropertyString('Label' . $property),
                'available' => $this->HasVariable(self::VARS[$valueKey]),
                'valueKey' => $valueKey,
                'target' => $isTarget,
                'rangeMin' => $rangeMin,
                'rangeMax' => $rangeMax,
                'mid' => $mid,
                'shape' => $this->ReadPropertyString('Display' . $property . 'Shape'),
                'orientation' => $this->ReadPropertyString('Display' . $property . 'Orientation'),
                'x' => $this->ReadPropertyInteger('Position' . $property . 'X'),
                'y' => $this->ReadPropertyInteger('Position' . $property . 'Y'),
                'width' => $this->ReadPropertyInteger('Display' . $property . 'Width'),
                'start' => $this->ReadPropertyInteger('Display' . $property . 'StartAngle'),
                'sweep' => $this->ReadPropertyInteger('Display' . $property . 'SweepAngle'),
                'textOffsetX' => $this->ReadPropertyInteger('Display' . $property . 'TextOffsetX'),
                'textOffsetY' => $this->ReadPropertyInteger('Display' . $property . 'TextOffsetY'),
                'borderWidth' => $this->ReadPropertyInteger('Display' . $property . 'BorderWidth'),
                'borderColor' => $this->Col('Display' . $property . 'BorderColor', null),
                'styles' => ['label' => $textStyles[$key], 'value' => $textStyles[$key]],
                'lowColor' => $this->Col('Color' . $property . 'Low', '#3B82F6'),
                'highColor' => $this->Col('Color' . $property . 'High', '#F97316'),
                'step' => 1.0,
                'controllable' => false,
                'permissionKey' => null
            ];

            if ($isTarget) {
                $element['controlMin'] = $this->ReadPropertyFloat('RoomControlMin');
                $element['controlMax'] = $this->ReadPropertyFloat('RoomControlMax');
                $element['step'] = $this->ReadPropertyFloat('RoomControlStep');
                $element['controllable'] = $this->ReadPropertyBoolean('RoomControlEnabled');
                $element['permissionKey'] = 'permissionAllowed';
            }
            $elements[$key] = $element;
        }
        $permissionID = $this->ReadPropertyInteger('PermissionVar');
        if ($permissionID <= 0) {
            $permissionID = $this->ReadPropertyInteger('RoomPermissionVar');
        }
        $elements['permission'] = [
            'label' => $this->ReadPropertyString('LabelPermission'),
            'available' => $permissionID > 0 && IPS_VariableExists($permissionID),
            'valueKey' => 'permission',
            'target' => false,
            'shape' => 'text',
            'x' => $this->ReadPropertyInteger('PositionPermissionX'),
            'y' => $this->ReadPropertyInteger('PositionPermissionY'),
            'textOffsetX' => $this->ReadPropertyInteger('DisplayPermissionTextOffsetX'),
            'textOffsetY' => $this->ReadPropertyInteger('DisplayPermissionTextOffsetY'),
            'styles' => ['label' => $textStyles['permission'], 'value' => $textStyles['permission']]
        ];

        return [
            'config' => [
                'name'         => $this->ReadPropertyString('Name'),
                'namePosition' => $this->ReadPropertyString('NamePosition'),
                'nameOffsetX'  => $this->ReadPropertyInteger('NameOffsetX'),
                'nameOffsetY'  => $this->ReadPropertyInteger('NameOffsetY'),
                'elements' => $elements,
                'textStyles'   => $textStyles,
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
