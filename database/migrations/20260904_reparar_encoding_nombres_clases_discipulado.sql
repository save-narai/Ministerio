-- Reparación de codificación de los nombres de las 9 clases de discipulado.
--
-- SÍNTOMA REPORTADO: las clases 3, 5 y 8 (las que llevan tildes o ¿)
-- se ven con caracteres extraños (mojibake) tanto en "Administrar clases"
-- como en "Materiales". Las clases sin tildes (4, 6, 7...) se ven bien.
-- Ese patrón es la firma clásica de una conexión PDO que no está
-- declarando charset=utf8mb4: la tabla SÍ está en utf8mb4 (se
-- confirmó en las migraciones anteriores), pero si PHP se conecta
-- sin indicar el charset, MySQL entrega los bytes ya convertidos
-- a otro charset y los acentos/símbolos se rompen SOLO en pantalla.
--
-- ESTE SCRIPT es un respaldo para el otro escenario, más grave: que
-- el problema haya ocurrido también al GUARDAR (por ejemplo, al
-- generarse las clases de un ciclo ya existente copiando el nombre
-- desde el catálogo con la misma conexión mal configurada), en cuyo
-- caso los bytes incorrectos ya quedaron guardados y no basta con
-- arreglar la conexión. Es seguro ejecutarlo aunque el problema
-- fuera solo de conexión: simplemente vuelve a dejar el texto
-- correcto donde ya estaba correcto.
--
-- IMPORTANTE: antes de ejecutar esto, revisa/corrige la conexión en
-- config/conexion.php (ver instrucciones en la respuesta del chat).
-- Si solo arreglas la conexión y no corres este script, y el
-- problema alcanzó a guardarse mal, las clases 3/5/8 seguirán
-- viéndose mal aunque la conexión ya esté bien.

START TRANSACTION;

-- 1) Catálogo reutilizable (clases_base_discipulado)
UPDATE clases_base_discipulado
SET nombre = CASE numero_orden
    WHEN 1 THEN 'Ciencia versus Dios'
    WHEN 2 THEN 'Fe ciega'
    WHEN 3 THEN 'Quién necesita la salvación'
    WHEN 4 THEN 'Jesucristo, regalo de Dios'
    WHEN 5 THEN '¿Qué es nacer de nuevo?'
    WHEN 6 THEN 'Libro de libros'
    WHEN 7 THEN 'Cristianismo, un culto irracional'
    WHEN 8 THEN '¿Cómo debo manejar mi vida?'
    WHEN 9 THEN 'Mentiras, verdad'
    ELSE nombre
END
WHERE numero_orden BETWEEN 1 AND 9;

-- 2) Clases ya generadas dentro de cada ciclo existente (clases_discipulado)
--    Se corrige por numero_orden en TODOS los ciclos, no solo en uno,
--    para no dejar ciclos viejos con el nombre roto.
UPDATE clases_discipulado
SET nombre = CASE numero_orden
    WHEN 1 THEN 'Ciencia versus Dios'
    WHEN 2 THEN 'Fe ciega'
    WHEN 3 THEN 'Quién necesita la salvación'
    WHEN 4 THEN 'Jesucristo, regalo de Dios'
    WHEN 5 THEN '¿Qué es nacer de nuevo?'
    WHEN 6 THEN 'Libro de libros'
    WHEN 7 THEN 'Cristianismo, un culto irracional'
    WHEN 8 THEN '¿Cómo debo manejar mi vida?'
    WHEN 9 THEN 'Mentiras, verdad'
    ELSE nombre
END
WHERE numero_orden BETWEEN 1 AND 9;

COMMIT;
