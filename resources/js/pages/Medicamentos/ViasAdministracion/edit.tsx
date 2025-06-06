import Form from './form';

interface ViaAdministracion {
    id: number;
    nombre: string;
    descripcion?: string;
    activo: boolean;
}

interface Props {
    viaAdministracion: ViaAdministracion;
}

export default function Edit({ viaAdministracion }: Props) {
    return <Form viaAdministracion={viaAdministracion} isEdit={true} />;
} 