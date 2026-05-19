-- ============================================================
-- CVAT Brasil — Dados iniciais (seed)
-- Execute após schema.sql:
--   mysql -u usuario -p nome_do_banco < seed.sql
-- ============================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- Treinamentos
-- ------------------------------------------------------------
INSERT INTO `treinamentos`
  (`titulo`, `descricao`, `modalidade`, `cor`, `categorias`, `publico`, `topicos`, `cta_texto`, `cta_link`, `ordem`)
VALUES
(
  'NR-01 na Prática',
  'Capacitação completa sobre a NR-01 atualizada: identificação de riscos psicossociais, elaboração do PGR e conformidade legal.',
  'Online ao vivo', 'blue',
  '["rh"]',
  'RH, SESMT e Gestores de SST',
  '["O que muda com a atualização de 2025","Como identificar riscos psicossociais","Elaboração do PGR passo a passo","Documentação e evidências para fiscalização"]',
  'Solicitar treinamento', '/pages/contato.html', 1
),
(
  'Liderança e Saúde Mental',
  'Como líderes podem identificar sinais de sofrimento psíquico, criar ambientes seguros e agir preventivamente sem substituir o papel clínico.',
  'Presencial ou Online', 'blue',
  '["lideranca"]',
  'Líderes e Gestores',
  '["Sinais de alerta em equipes","Comunicação empática e não violenta","Segurança psicológica na prática","Quando e como encaminhar para suporte"]',
  'Solicitar treinamento', '/pages/contato.html', 2
),
(
  'Prevenção ao Burnout',
  'Reconhecimento precoce, fatores organizacionais de risco e estratégias de prevenção baseadas em evidências para equipes e lideranças.',
  'Online ao vivo', 'green',
  '["rh","equipes"]',
  'Equipes e RH',
  '["O que é burnout e o que não é","Fatores organizacionais de risco","Prevenção primária, secundária e terciária","Plano de ação para sua equipe"]',
  'Solicitar treinamento', '/pages/contato.html', 3
),
(
  'COPSOQ III na Prática',
  'Como aplicar, tabular e interpretar o COPSOQ III no contexto brasileiro — da coleta à devolutiva para a liderança.',
  'Online ao vivo', 'coral',
  '["rh"]',
  'RH e Consultores',
  '["Estrutura e dimensões do COPSOQ III","Aplicação e gestão da coleta","Análise e benchmarking nacional","Como apresentar os resultados"]',
  'Solicitar treinamento', '/pages/contato.html', 4
),
(
  'Segurança Psicológica',
  'Baseado na pesquisa de Amy Edmondson e no modelo do Google: como criar times onde as pessoas se sentem seguras para contribuir e inovar.',
  'Presencial ou Online', 'green',
  '["lideranca","equipes"]',
  'Líderes e Equipes',
  '["O que é e o que não é segurança psicológica","Os 4 estágios do modelo Edmondson","Práticas de liderança que constroem confiança","Como medir e acompanhar a evolução"]',
  'Solicitar treinamento', '/pages/contato.html', 5
),
(
  'Trabalho Remoto e Saúde Mental',
  'Desafios específicos do trabalho remoto e híbrido: isolamento, fronteiras trabalho-vida, comunicação assíncrona e bem-estar digital.',
  'EAD (assíncrono)', 'coral',
  '["equipes"]',
  'Equipes Remotas e RH',
  '["Riscos específicos do trabalho remoto","Rotinas e rituais de equipe à distância","Ergonomia digital e desconexão","Políticas de home office saudável"]',
  'Solicitar treinamento', '/pages/contato.html', 6
);

-- ------------------------------------------------------------
-- Blog posts
-- ------------------------------------------------------------
INSERT INTO `blog_posts`
  (`destaque`, `titulo`, `excerpt`, `categoria`, `categoria_label`, `cor`,
   `data_publicacao`, `tempo_leitura`, `autor_nome`, `autor_iniciais`, `autor_cor`, `link`)
VALUES
(
  1,
  'NR-01 atualizada: tudo que sua empresa precisa documentar antes da fiscalização do MTE',
  'A atualização de 2025 incluiu os riscos psicossociais como categoria obrigatória do PGR. Neste guia completo, detalhamos cada obrigação, os instrumentos aceitos e como montar a documentação para não tomar autuação.',
  'nr01', 'NR-01', 'blue', '2025-04-10', 8, 'Dra. Patrícia Costa', 'PC', 'default', '#'
),
(
  0,
  'Burnout no trabalho: como identificar antes que vire afastamento',
  'Os sinais de burnout aparecem meses antes do colapso. Saiba o que observar nos dados de clima, nas conversas de 1:1 e no comportamento da equipe.',
  'saude-mental', 'Saúde Mental', 'green', '2025-03-28', 6, 'Juliana Santana', 'JS', 'green', '#'
),
(
  0,
  'Por que pesquisa de clima tem baixa taxa de resposta — e como resolver',
  'Taxa abaixo de 50% invalida os resultados. Conheça os fatores que inibem a participação e as ações que dobram o engajamento na pesquisa.',
  'clima', 'Clima Organizacional', 'blue', '2025-03-15', 5, 'Ricardo Alves', 'RA', 'default', '#'
),
(
  0,
  'Segurança psicológica: o que todo líder precisa saber em 2025',
  'O conceito de Amy Edmondson chegou às empresas brasileiras — mas poucos líderes sabem como construí-lo na prática. Veja o que realmente funciona.',
  'lideranca', 'Liderança', 'coral', '2025-03-05', 7, 'Juliana Santana', 'JS', 'green', '#'
),
(
  0,
  'COPSOQ III: guia prático para aplicar em empresas brasileiras',
  'O instrumento mais aceito para avaliação de riscos psicossociais. Como adaptar, aplicar, tabular e interpretar os resultados dentro da realidade do mercado nacional.',
  'nr01', 'NR-01', 'blue', '2025-02-20', 10, 'Dra. Patrícia Costa', 'PC', 'green', '#'
),
(
  0,
  'Como apresentar dados de clima para a diretoria e sair com orçamento aprovado',
  'A diferença entre um relatório que vai para a gaveta e um que gera investimento está na forma de apresentar — não nos dados. Aprenda a estrutura que funciona.',
  'rh', 'RH Estratégico', 'green', '2025-02-10', 5, 'Ricardo Alves', 'RA', 'default', '#'
),
(
  0,
  'Trabalho remoto e saúde mental: o que 3 anos de dados mostram',
  'Analisamos os dados de diagnóstico de mais de 15 mil colaboradores em regime remoto ou híbrido. Os resultados surpreendem — e obrigam repensar as políticas de home office.',
  'saude-mental', 'Saúde Mental', 'coral', '2025-01-28', 6, 'Dra. Patrícia Costa', 'PC', 'green', '#'
);
