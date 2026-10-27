<?php

return [
    'required' => ':attribute tidak boleh kosong.',
    'string' => ':attribute harus berupa teks.',
    'max' => [
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
        'file' => ':attribute tidak boleh lebih dari :max kilobyte.',
    ],
    'min' => [
        'string' => ':attribute minimal harus berisi :min karakter.',
        'file' => ':attribute minimal berukuran :min kilobyte.',
    ],
    'unique' => ':attribute sudah digunakan.',
    'image' => ':attribute harus berupa gambar.',
    'mimes' => ':attribute harus berupa file dengan tipe: :values.',
    'mimetypes' => ':attribute harus berupa file dengan tipe: :values.',
    'attributes' => [
        'title' => 'judul artikel',
        'slug' => 'slug',
        'content' => 'konten artikel',
        'thumbnail' => 'gambar thumbnail',
        'status' => 'status artikel',
        'name' => 'nama kategori',
        'category_id' => 'kategori',
        'password' => 'password',
        'foto' => 'foto',
        'foto2' => 'foto',
    ],
];
