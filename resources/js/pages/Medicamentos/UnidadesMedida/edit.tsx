import Form from './form';

interface UnidadMedida {
    id: number;
    nombre: string;
    simbolo: string;
    tipo: string;
    descripcion?: string;
    activo: boolean;
}

interface Props {
    unidadMedida: UnidadMedida;
}

export default function Edit({ unidadMedida }: Props) {
    return <Form unidadMedida={unidadMedida} isEdit={true} />;
} 