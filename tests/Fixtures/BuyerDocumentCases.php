<?php

namespace Tests\Fixtures;

final class BuyerDocumentCases
{
    public static function invalid(): array
    {
        return [
            'tipo no admitido' => ['RUC', '12345678901', 'document_type'],
            'tipo no escalar' => [['DNI'], '12345678', 'document_type'],
            'DNI corto' => ['DNI', '1234567', 'document_number'],
            'DNI largo' => ['DNI', '123456789', 'document_number'],
            'DNI con letra' => ['DNI', '1234567A', 'document_number'],
            'DNI con espacio interno' => ['DNI', '1234 678', 'document_number'],
            'DNI numerico en JSON' => ['DNI', 12345678, 'document_number'],
            'CE corto' => ['CE', 'AB123', 'document_number'],
            'CE largo' => ['CE', str_repeat('A', 21), 'document_number'],
            'CE con guion' => ['CE', 'AB-123', 'document_number'],
            'pasaporte corto' => ['PASAPORTE', 'AB123', 'document_number'],
            'pasaporte largo' => ['PASAPORTE', str_repeat('A', 21), 'document_number'],
            'pasaporte con simbolo' => ['PASAPORTE', 'AB_123', 'document_number'],
            'pasaporte con letras no ASCII' => ['PASAPORTE', 'AÑ1234', 'document_number'],
            'numero no escalar' => ['DNI', ['12345678'], 'document_number'],
        ];
    }

    public static function valid(): array
    {
        return [
            'DNI con ceros y espacios' => [' dni ', ' 00000001 ', 'DNI', '00000001'],
            'CE minimo' => [' ce ', ' ab1234 ', 'CE', 'AB1234'],
            'CE maximo' => ['CE', str_repeat('a', 20), 'CE', str_repeat('A', 20)],
            'pasaporte minimo' => [' pasaporte ', ' ab1234 ', 'PASAPORTE', 'AB1234'],
            'pasaporte maximo' => ['PASAPORTE', str_repeat('a', 20), 'PASAPORTE', str_repeat('A', 20)],
        ];
    }
}
