<?php
/** Tenant-owned store access policy, enforced before every uploader implementation. */
final class ProtectedMedia
{
    public static function authorize($req)
    {
        $params=$req->Params();
        if (($params->componentName ?? '')!=='soss-uploader') return;
        $parts=explode('/',trim(rawurldecode($params->route ?? ''),'/'));
        if (count($parts)!==2 || !preg_match('/^[A-Za-z0-9_-]+$/D',$parts[0]) || !preg_match('/^[A-Za-z0-9_-][A-Za-z0-9._-]*$/D',$parts[1]) || strpos($parts[1],'..')!==false) throw new RuntimeException('Invalid media reference.');
        $file=TENANT_RESOURCE_LOCATION.'/global/config/media-access.json';
        if(!is_file($file))return;
        $policies=json_decode(file_get_contents($file));$policy=$policies->{$parts[0]} ?? null;
        if(!$policy)return;
        $root=realpath(TENANT_RESOURCE_LOCATION);$implementation=realpath(TENANT_RESOURCE_LOCATION.'/'.$policy->file);
        if(!$implementation || strpos($implementation,$root.DIRECTORY_SEPARATOR)!==0)throw new RuntimeException('Media access policy is unavailable.');
        require_once $implementation;
        $class=$policy->class;
        if(!class_exists($class) || !$class::authorize($parts[0],$parts[1],strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),strtolower($params->handlerName ?? '')))throw new RuntimeException('Media access denied.');
    }
}
