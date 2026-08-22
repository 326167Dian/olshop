<?php

return [
    'required' => ':attribute tidak boleh kosong.',
    'string' => ':attribute harus berupa teks.',
    'max' => [
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
    ],
    'min' => [
        'string' => ':attribute minimal harus berisi :min karakter.',
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
    ],
];
