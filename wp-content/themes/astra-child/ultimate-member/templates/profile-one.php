<?php
/* Template: Profile One */
if (!defined('ABSPATH')) {
	exit;
}

// Obtener datos del usuario
$user_id = um_profile_id();
$user = get_userdata($user_id);
$description_key = UM()->profile()->get_show_bio_key($args);

// Obtener puntos del usuario (asumiendo que usas GamiPress)
$user_points = function_exists('gamipress_get_user_points') ?
	gamipress_get_user_points($user_id, 'puntos') : 1250;

// Obtener seguidores (asumiendo que usas Ultimate Member Followers)
$followers = function_exists('um_followers_count') ?
	um_followers_count($user_id) : 3500;

// Obtener otros datos del perfil
$location = get_user_meta($user_id, 'ubicacion', true);
$job_title = get_user_meta($user_id, 'puesto_trabajo', true);
$member_since = date_i18n('j F, Y', strtotime($user->user_registered));

// Obtener habilidades
$skills = get_user_meta($user_id, 'habilidades', true);
if (!is_array($skills)) {
	$skills = explode(',', $skills);
}

// Obtener logros (badges) - ejemplo con GamiPress
$achievements = array();
if (function_exists('gamipress_get_user_achievements')) {
	$achievements = gamipress_get_user_achievements(array(
		'user_id' => $user_id,
		'achievement_type' => 'badge',
		'limit' => 6
	));
} else {
	// Datos de ejemplo si GamiPress no está instalado
	$achievements = array(
		(object) array('achievement_title' => 'Principiante', 'points' => 100),
		(object) array('achievement_title' => 'Desarrollador', 'points' => 250),
		(object) array('achievement_title' => 'Comunicador', 'points' => 75),
		(object) array('achievement_title' => 'Innovador', 'points' => 150),
		(object) array('achievement_title' => 'Experto', 'points' => 500),
		(object) array('achievement_title' => 'Campeón', 'points' => 1000)
	);
}

// Obtener actividad reciente - ejemplo
$activities = array(
	array(
		'type' => 'logro',
		'title' => 'Nuevo logro desbloqueado',
		'description' => 'Has ganado el badge "Experto en React"',
		'icon' => 'medal',
		'meta' => array(
			'time' => 'Hace 2 horas',
			'points' => '+50 puntos'
		)
	),
	array(
		'type' => 'proyecto',
		'title' => 'Proyecto publicado',
		'description' => 'Has publicado un nuevo proyecto "Dashboard Analytics"',
		'icon' => 'code',
		'meta' => array(
			'time' => 'Ayer',
			'likes' => '24 me gusta'
		)
	),
	array(
		'type' => 'comentario',
		'title' => 'Nuevo comentario',
		'description' => 'Comentaste en el proyecto de Juan Pérez',
		'icon' => 'comment',
		'meta' => array(
			'time' => '15 de junio',
			'replies' => '3 respuestas'
		)
	)
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Perfil de <?php echo esc_html(um_user('display_name')); ?></title>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<style>
		:root {
			--primary: #4361ee;
			--primary-light: #4895ef;
			--secondary: #3f37c9;
			--dark: #1a202c;
			--light: #f8fafd;
			--gray: #718096;
			--light-gray: #e2e8f0;
			--success: #38b000;
			--danger: #e63946;
			--warning: #ff9e00;
			--border-radius: 12px;
			--shadow: 0 8px 30px rgba(0,0,0,0.08);
			--transition: all 0.3s ease;
		}
		
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
			font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
		}
		
		body {
			background-color: #f0f4f8;
			color: var(--dark);
			line-height: 1.6;
			padding: 20px;
		}
		
		.profile-container {
			max-width: 1200px;
			margin: 0 auto;
			background: white;
			border-radius: var(--border-radius);
			overflow: hidden;
			box-shadow: var(--shadow);
		}
		
		/* Cover Section */
		.profile-cover {
			height: 280px;
			background: linear-gradient(135deg, var(--primary), var(--secondary));
			position: relative;
			display: flex;
			align-items: flex-end;
			padding: 0 30px 100px;
		}
		
		.cover-actions {
			position: absolute;
			top: 20px;
			right: 30px;
			display: flex;
			gap: 12px;
		}
		
		.btn {
			padding: 10px 20px;
			border-radius: 50px;
			font-weight: 600;
			cursor: pointer;
			border: none;
			transition: var(--transition);
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}
		
		.btn-primary {
			background: white;
			color: var(--primary);
		}
		
		.btn-primary:hover {
			background: var(--light);
			transform: translateY(-2px);
		}
		
		.btn-secondary {
			background: rgba(255, 255, 255, 0.2);
			color: white;
			backdrop-filter: blur(10px);
		}
		
		.btn-secondary:hover {
			background: rgba(255, 255, 255, 0.3);
		}
		
		/* Profile Header */
		.profile-header {
			display: flex;
			padding: 0 30px 30px;
			margin-top: -80px;
			position: relative;
		}
		
		.profile-avatar {
			width: 160px;
			height: 160px;
			border-radius: 50%;
			border: 5px solid white;
			overflow: hidden;
			box-shadow: var(--shadow);
			background: white;
			flex-shrink: 0;
		}
		
		.profile-avatar img {
			width: 100%;
			height: 100%;
			object-fit: cover;
		}
		
		.profile-info {
			flex-grow: 1;
			padding: 30px 0 0 40px;
		}
		
		.profile-name {
			font-size: 2.2rem;
			font-weight: 800;
			margin-bottom: 8px;
			color: var(--dark);
		}
		
		.profile-title {
			font-size: 1.1rem;
			color: var(--gray);
			margin-bottom: 20px;
		}
		
		.profile-stats {
			display: flex;
			gap: 30px;
			margin-top: 20px;
		}
		
		.stat-item {
			text-align: center;
		}
		
		.stat-value {
			font-size: 1.5rem;
			font-weight: 700;
			color: var(--primary);
		}
		
		.stat-label {
			font-size: 0.9rem;
			color: var(--gray);
		}
		
		/* Profile Navigation */
		.profile-navbar {
			border-top: 1px solid var(--light-gray);
			border-bottom: 1px solid var(--light-gray);
			padding: 0 30px;
			background: white;
		}
		
		.profile-tabs {
			display: flex;
			list-style: none;
		}
		
		.profile-tab {
			padding: 20px 25px;
			font-weight: 600;
			color: var(--gray);
			cursor: pointer;
			position: relative;
			transition: var(--transition);
		}
		
		.profile-tab:hover {
			color: var(--primary);
		}
		
		.profile-tab.active {
			color: var(--primary);
		}
		
		.profile-tab.active::after {
			content: '';
			position: absolute;
			bottom: 0;
			left: 0;
			right: 0;
			height: 3px;
			background: var(--primary);
			border-radius: 3px 3px 0 0;
		}
		
		/* Profile Content */
		.profile-body {
			padding: 30px;
			display: grid;
			grid-template-columns: 1fr 350px;
			gap: 30px;
		}
		
		.profile-main {
			background: white;
			border-radius: var(--border-radius);
			padding: 30px;
			box-shadow: var(--shadow);
		}
		
		.profile-sidebar {
			display: flex;
			flex-direction: column;
			gap: 30px;
		}
		
		.sidebar-card {
			background: white;
			border-radius: var(--border-radius);
			padding: 25px;
			box-shadow: var(--shadow);
		}
		
		.card-header {
			display: flex;
			justify-content: space-between;
			align-items: center;
			margin-bottom: 20px;
			padding-bottom: 15px;
			border-bottom: 1px solid var(--light-gray);
		}
		
		.card-title {
			font-size: 1.2rem;
			font-weight: 700;
			color: var(--dark);
		}
		
		.card-action {
			color: var(--primary);
			font-weight: 600;
			cursor: pointer;
			font-size: 0.9rem;
		}
		
		.card-action:hover {
			text-decoration: underline;
		}
		
		/* About Section */
		.about-item {
			display: flex;
			margin-bottom: 20px;
		}
		
		.about-icon {
			width: 40px;
			height: 40px;
			background: var(--light);
			border-radius: 10px;
			display: flex;
			align-items: center;
			justify-content: center;
			color: var(--primary);
			margin-right: 15px;
			flex-shrink: 0;
		}
		
		.about-content h4 {
			font-size: 1rem;
			margin-bottom: 5px;
			color: var(--gray);
		}
		
		.about-content p {
			font-weight: 500;
		}
		
		/* Skills Section */
		.skills-container {
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
		}
		
		.skill-tag {
			background: var(--light);
			color: var(--primary);
			padding: 8px 15px;
			border-radius: 50px;
			font-size: 0.9rem;
			font-weight: 500;
		}
		
		/* Activity Section */
		.activity-item {
			display: flex;
			gap: 15px;
			padding: 15px 0;
			border-bottom: 1px solid var(--light-gray);
		}
		
		.activity-item:last-child {
			border-bottom: none;
		}
		
		.activity-icon {
			width: 45px;
			height: 45px;
			background: var(--light);
			border-radius: 12px;
			display: flex;
			align-items: center;
			justify-content: center;
			color: var(--primary);
			flex-shrink: 0;
		}
		
		.activity-content {
			flex-grow: 1;
		}
		
		.activity-title {
			font-weight: 600;
			margin-bottom: 5px;
		}
		
		.activity-meta {
			font-size: 0.85rem;
			color: var(--gray);
			display: flex;
			gap: 15px;
		}
		
		/* Badges Section */
		.badges-container {
			display: grid;
			grid-template-columns: repeat(3, 1fr);
			gap: 15px;
		}
		
		.badge-item {
			background: var(--light);
			border-radius: 12px;
			padding: 15px;
			text-align: center;
			transition: var(--transition);
		}
		
		.badge-item:hover {
			transform: translateY(-5px);
		}
		
		.badge-icon {
			width: 60px;
			height: 60px;
			background: white;
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			margin: 0 auto 15px;
			color: var(--warning);
			font-size: 1.8rem;
			box-shadow: 0 5px 15px rgba(0,0,0,0.05);
		}
		
		.badge-name {
			font-weight: 600;
			font-size: 0.9rem;
		}
		
		.badge-points {
			font-size: 0.8rem;
			color: var(--gray);
		}
		
		/* Responsive Design */
		@media (max-width: 992px) {
			.profile-body {
				grid-template-columns: 1fr;
			}
			
			.profile-header {
				flex-direction: column;
				align-items: center;
				text-align: center;
			}
			
			.profile-info {
				padding-left: 0;
				margin-top: 20px;
			}
			
			.profile-stats {
				justify-content: center;
			}
		}
		
		@media (max-width: 768px) {
			.profile-cover {
				height: 220px;
				padding-bottom: 80px;
			}
			
			.profile-avatar {
				width: 120px;
				height: 120px;
			}
			
			.profile-tabs {
				overflow-x: auto;
				padding-bottom: 5px;
			}
			
			.badges-container {
				grid-template-columns: repeat(2, 1fr);
			}
		}
		
		@media (max-width: 576px) {
			.cover-actions {
				position: static;
				margin-top: 20px;
				justify-content: center;
			}
			
			.profile-cover {
				flex-direction: column;
				align-items: center;
				padding-bottom: 30px;
			}
			
			.profile-stats {
				flex-wrap: wrap;
				gap: 15px;
			}
		}
	</style>
</head>
<body>
	<div class="profile-container">
		<!-- Cover Section -->
		<div class="profile-cover">
			<div class="cover-actions">
				<?php if (um_is_on_edit_profile()): ?>
						<button class="btn btn-secondary">
							<i class="fas fa-cog"></i> Configuración
						</button>
				<?php else: ?>
						<?php if (is_user_logged_in() && get_current_user_id() == $user_id): ?>
								<a href="<?php echo esc_url(um_edit_profile_url()); ?>" class="btn btn-secondary">
									<i class="fas fa-cog"></i> Configuración
								</a>
						<?php endif; ?>
				<?php endif; ?>

				<?php if (function_exists('um_followers_button')): ?>
						<div class="btn btn-primary">
							<?php echo um_followers_button($user_id, get_current_user_id()); ?>
						</div>
				<?php endif; ?>
			</div>
		</div>
		
		<!-- Profile Header -->
		<div class="profile-header">
			<div class="profile-avatar">
				<?php echo get_avatar($user_id, 160); ?>
			</div>
			
			<div class="profile-info">
				<h1 class="profile-name"><?php echo esc_html(um_user('display_name')); ?></h1>
				<p class="profile-title"><?php echo esc_html($job_title); ?></p>
				
				<div class="profile-stats">
					<div class="stat-item">
						<div class="stat-value"><?php echo number_format_i18n($user_points); ?></div>
						<div class="stat-label">Puntos</div>
					</div>
					<div class="stat-item">
						<div class="stat-value"><?php echo count_user_posts($user_id); ?></div>
						<div class="stat-label">Proyectos</div>
					</div>
					<div class="stat-item">
						<div class="stat-value"><?php echo count($achievements); ?></div>
						<div class="stat-label">Logros</div>
					</div>
					<div class="stat-item">
						<div class="stat-value"><?php echo number_format_i18n($followers); ?></div>
						<div class="stat-label">Seguidores</div>
					</div>
				</div>
			</div>
		</div>
		
		<!-- Navigation -->
		<div class="profile-navbar">
			<ul class="profile-tabs">
				<li class="profile-tab active">Inicio</li>
				<li class="profile-tab">Sobre mí</li>
				<li class="profile-tab">Actividad</li>
				<li class="profile-tab">Proyectos</li>
				<li class="profile-tab">Amigos</li>
				<li class="profile-tab">Logros</li>
				<li class="profile-tab">Configuración</li>
			</ul>
		</div>
		
		<!-- Profile Content -->
		<div class="profile-body">
			<div class="profile-main">
				<div class="card-header">
					<h2 class="card-title">Sobre mí</h2>
				</div>
				
				<p style="margin-bottom: 25px; color: #4a5568;">
					<?php echo esc_html(um_user('description')); ?>
				</p>
				
				<?php if ($location): ?>
					<div class="about-item">
						<div class="about-icon">
							<i class="fas fa-map-marker-alt"></i>
						</div>
						<div class="about-content">
							<h4>Ubicación</h4>
							<p><?php echo esc_html($location); ?></p>
						</div>
					</div>
				<?php endif; ?>
				
				<?php if ($job_title): ?>
					<div class="about-item">
						<div class="about-icon">
							<i class="fas fa-briefcase"></i>
						</div>
						<div class="about-content">
							<h4>Empleo</h4>
							<p><?php echo esc_html($job_title); ?></p>
						</div>
					</div>
				<?php endif; ?>
				
				<div class="about-item">
					<div class="about-icon">
						<i class="fas fa-calendar"></i>
					</div>
					<div class="about-content">
						<h4>Miembro desde</h4>
						<p><?php echo esc_html($member_since); ?></p>
					</div>
				</div>
				
				<?php if (!empty($skills)): ?>
					<div class="card-header" style="margin-top: 35px;">
						<h2 class="card-title">Habilidades</h2>
					</div>
				
					<div class="skills-container">
						<?php foreach ($skills as $skill): ?>
								<div class="skill-tag"><?php echo esc_html(trim($skill)); ?></div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			
			<div class="profile-sidebar">
				<div class="sidebar-card">
					<div class="card-header">
						<h2 class="card-title">Actividad Reciente</h2>
						<div class="card-action">Ver todo</div>
					</div>
					
					<?php foreach ($activities as $activity): ?>
						<div class="activity-item">
							<div class="activity-icon">
								<i class="fas fa-<?php echo esc_attr($activity['icon']); ?>"></i>
							</div>
							<div class="activity-content">
								<div class="activity-title"><?php echo esc_html($activity['title']); ?></div>
								<p><?php echo esc_html($activity['description']); ?></p>
								<div class="activity-meta">
									<?php foreach ($activity['meta'] as $key => $value): ?>
											<span><?php echo esc_html($value); ?></span>
									<?php endforeach; ?>
								</div>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
				
				<div class="sidebar-card">
					<div class="card-header">
						<h2 class="card-title">Logros</h2>
						<div class="card-action">Ver todos</div>
					</div>
					
					<div class="badges-container">
						<?php foreach ($achievements as $achievement): ?>
							<div class="badge-item">
								<div class="badge-icon">
									<i class="fas fa-medal"></i>
								</div>
								<div class="badge-name">
									<?php echo esc_html($achievement->achievement_title); ?>
								</div>
								<div class="badge-points">
									+<?php echo isset($achievement->points) ? esc_html($achievement->points) : '100'; ?> puntos
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<script>
		// Tab navigation functionality
		document.querySelectorAll('.profile-tab').forEach(tab => {
			tab.addEventListener('click', () => {
				// Remove active class from all tabs
				document.querySelectorAll('.profile-tab').forEach(t => {
					t.classList.remove('active');
				});
				
				// Add active class to clicked tab
				tab.classList.add('active');
				
				// Aquí iría la lógica para cargar contenido dinámico basado en la pestaña seleccionada
				console.log('Cambiando a pestaña: ' + tab.textContent);
			});
		});
	</script>
</body>
</html>