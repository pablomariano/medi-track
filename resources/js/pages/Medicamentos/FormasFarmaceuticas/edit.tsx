import Form from './form';

interface FormaFarmaceutica {
    id: number;
    nombre: string;
    descripcion?: string;
    activo: boolean;
}

interface Props {
    formaFarmaceutica: FormaFarmaceutica;
}

export default function Edit({ formaFarmaceutica }: Props) {
    return <Form formaFarmaceutica={formaFarmaceutica} isEdit={true} />;
} 