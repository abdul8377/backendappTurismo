<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaProducto extends Model
{
    use HasFactory;

    // Nombre de la tabla
    protected $table = 'categorias_productos';

    // Campos que pueden ser asignados en masa

       // Especificar la clave primaria si no es 'id'
    protected $primaryKey = 'categorias_productos_id'; // Aquí especificas la clave primaria correcta

    protected $fillable = ['nombre', 'descripcion'];

    // Desactivar las marcas de tiempo si no las necesitas
    public $timestamps = true;

    // Relación con los productos
    public function productos()
    {
        // Asegúrate de que los campos de clave foránea y primaria estén correctamente asignados
        return $this->hasMany(Producto::class, 'categorias_productos_id', 'categorias_productos_id');
    }
}
