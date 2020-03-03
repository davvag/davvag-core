CREATE PROCEDURE getnearproducts
(
    param_lat DECIMAL(10, 8),
    param_lng DECIMAL(11, 8),
    param_catid INT,
    param_radius INT
)
BEGIN
SELECT 
    products.itemid,
    products.caption,
    products.catogory,
    products.imgurl,
    products.name,
    products.price,
    products.status,
    products.uom,
    products.sysversionid
    ( 6371 *
    ACOS( COS(RADIANS(latitude)) * COS(RADIANS(param_lat)) * 
    COS( RADIANS( param_lng ) - RADIANS( longitude ) ) +
    SIN( RADIANS( latitude) ) * SIN( RADIANS( param_lat) )
    ))
    AS distance FROM store 
INNER JOIN storeproductmapping ON storeproductmapping.storeid = store.id
INNER JOIN products ON storeproductmapping.productid = products.id
HAVING distance <= param_radius ORDER BY distance ASC
END;
////////////////////////////////////////////////////////////////////////////
CREATE PROCEDURE getnearproducts
(
    param_lat DECIMAL(10, 8),
    param_lng DECIMAL(11, 8),
    param_catid INT,
    param_radius INT
)

BEGIN
	
DECLARE var_lan1 decimal(11,8);
DECLARE var_lan2 decimal(11,8);
DECLARE var_lat1 decimal(10,8);
DECLARE var_lat2 decimal(10,8);
SET var_lan1 =param_lng-(1/111*param_radius);
SET var_lan2 =param_lng+(1/111*param_radius);
SET var_lat1 =param_lat-(1/111*param_radius);
SET var_lat2 =param_lat+(1/111*param_radius);

SELECT * FROM ((products INNER JOIN storeproductmapping ON products.itemid=storeproductmapping.productid) INNER JOIN store ON store.id = storeproductmapping.Storeid) where store.latitude between var_lat1 and var_lat2 and store.longitude between var_lan1 and var_lan2 AND products.catogory=param_catid;
END